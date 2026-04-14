<?php

namespace App\Http\Controllers;

use App\Models\SystemRecord;
use App\Models\SystemStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SystemRecordController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('systems.view'), 403);

        $systems = SystemRecord::query()
            ->with(['links', 'attachments', 'status'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statuses = SystemStatus::query()->orderBy('name')->get();

        return view('systems.index', compact('systems', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('systems.create'), 403);

        $data = $this->validatedData($request);

        $system = SystemRecord::query()->create([
            'name' => $data['name'],
            'trello_url' => $data['trello_url'] ?? null,
            'system_status_id' => $data['system_status_id'],
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
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'system_status_id' => ['required', 'exists:system_statuses,id'],
            'link' => ['nullable', 'url', 'max:2048'],
            'trello_url' => ['nullable', 'url', 'max:2048'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);
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