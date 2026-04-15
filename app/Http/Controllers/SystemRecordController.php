<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SystemRecord;
use App\Models\SystemStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SystemRecordController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('systems.view'), 403);

        $search = (string) $request->string('search');

        $systems = $this->buildFilteredSystemsQuery($request)
            ->with(['links', 'attachments'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statuses = SystemStatus::query()->orderBy('name')->get();

        return view('systems.index', compact('systems', 'statuses', 'search'));
    }

    public function report(Request $request, string $format): Response
    {
        abort_unless($request->user()->can('systems.view'), 403);

        $search = (string) $request->string('search');

        $systems = $this->buildFilteredSystemsQuery($request)
            ->withCount('attachments')
            ->latest()
            ->get();

        $generatedAt = now();
        $reportTitle = 'Reporte de sistemas';
        $filenameBase = 'reporte-sistemas-'.$generatedAt->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('systems.report-pdf', compact('systems', 'generatedAt', 'reportTitle', 'search'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filenameBase.'.pdf');
        }

        abort_unless($format === 'excel', 404);

        $content = view('systems.report-excel', compact('systems', 'generatedAt', 'reportTitle', 'search'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.xls"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('systems.create'), 403);

        $data = $this->validatedData($request);

        $system = SystemRecord::query()->create([
            'name' => $data['name'],
            'trello_url' => $data['trello_url'] ?? null,
            'system_status_id' => $data['system_status_id'],
            'pending_errors' => $data['pending_errors'],
            'errors_in_progress' => $data['errors_in_progress'],
            'in_review' => $data['in_review'],
            'finalized' => $data['finalized'],
            'created_by' => $request->user()->id,
        ]);

        $this->syncLink($system, $data['link'] ?? null, $request->user()->id);
        $this->syncAttachments($system, $request->file('attachments', []), $request->user()->id);

        return redirect()->route('systems.index')->with('status', 'Sistema creado correctamente.');
    }

    public function update(Request $request, SystemRecord $system): RedirectResponse
    {
        abort_unless($request->user()->can('systems.update'), 403);

        $data = $this->validatedData($request);

        $system->update([
            'name' => $data['name'],
            'trello_url' => $data['trello_url'] ?? null,
            'system_status_id' => $data['system_status_id'],
            'pending_errors' => $data['pending_errors'],
            'errors_in_progress' => $data['errors_in_progress'],
            'in_review' => $data['in_review'],
            'finalized' => $data['finalized'],
        ]);

        $this->syncLink($system, $data['link'] ?? null, $request->user()->id);
        $this->syncAttachments($system, $request->file('attachments', []), $request->user()->id);

        return redirect()->route('systems.index')->with('status', 'Sistema actualizado correctamente.');
    }

    public function destroy(Request $request, SystemRecord $system): RedirectResponse
    {
        abort_unless($request->user()->can('systems.delete'), 403);

        $system->links()->delete();
        $system->comments()->delete();
        foreach ($system->attachments as $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
            $attachment->delete();
        }
        $system->changeLogs()->delete();
        $system->delete();

        return redirect()->route('systems.index')->with('status', 'Sistema eliminado correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'system_status_id' => ['required', 'exists:system_statuses,id'],
            'link' => ['nullable', 'url', 'max:2048'],
            'trello_url' => ['nullable', 'url', 'max:2048'],
            'pending_errors' => ['nullable', 'integer', 'min:0'],
            'errors_in_progress' => ['nullable', 'integer', 'min:0'],
            'in_review' => ['nullable', 'integer', 'min:0'],
            'finalized' => ['nullable', 'integer', 'min:0'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $status = SystemStatus::query()->find($data['system_status_id']);

        if ($status?->slug === 'en-pruebas') {
            foreach (['pending_errors', 'errors_in_progress', 'in_review', 'finalized'] as $field) {
                if ($data[$field] === null) {
                    $data[$field] = 0;
                }
            }

            return $data;
        }

        $data['pending_errors'] = null;
        $data['errors_in_progress'] = null;
        $data['in_review'] = null;
        $data['finalized'] = null;

        return $data;
    }

    protected function buildFilteredSystemsQuery(Request $request): Builder
    {
        $search = (string) $request->string('search');

        return SystemRecord::query()
            ->with('status')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('status', fn ($statusQuery) => $statusQuery->where('name', 'like', "%{$search}%"));
                });
            });
    }

    protected function syncAttachments(SystemRecord $system, array $files, int $userId): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('attachments/system', 'public');

            $system->attachments()->create([
                'uploaded_by' => $userId,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    protected function syncLink(SystemRecord $system, ?string $url, int $userId): void
    {
        $currentLink = $system->links()->first();

        if (blank($url)) {
            if ($currentLink) {
                $currentLink->delete();
            }

            return;
        }

        if ($currentLink) {
            $currentLink->update([
                'label' => 'Sistema '.$system->name,
                'url' => $url,
            ]);

            return;
        }

        $system->links()->create([
            'label' => 'Sistema '.$system->name,
            'url' => $url,
            'created_by' => $userId,
        ]);
    }
}