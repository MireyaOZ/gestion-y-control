<?php

namespace App\Http\Controllers;

use App\Models\EmailCargo;
use App\Models\EmailMovementType;
use App\Models\EmailRequest;
use App\Services\ChangeLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class EmailRequestController extends Controller
{
    private const CHANGE_ARROW = '<span style="color:#2563eb;font-weight:700;">&rarr;</span>';

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('emails.view'), 403);

        [$cargos, $search, $selectedAreaId, $selectedArea, $areaOptions] = $this->emailsFilterContext($request);

        $emailRequests = $this->buildFilteredEmailRequestsQuery($request, $cargos)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $movementTypes = EmailMovementType::query()->orderBy('name')->get();

        return view('emails.index', compact('emailRequests', 'cargos', 'movementTypes', 'search', 'selectedAreaId', 'selectedArea', 'areaOptions'));
    }

    public function report(Request $request, string $format): Response
    {
        abort_unless($request->user()->can('emails.view'), 403);

        [$cargos, $search, $selectedAreaId, $selectedArea] = $this->emailsFilterContext($request);

        $emailRequests = $this->buildFilteredEmailRequestsQuery($request, $cargos)
            ->latest()
            ->get();

        $generatedAt = now();
        $reportTitle = 'Reporte de correos';
        $areaLabel = $selectedArea?->name ?? 'Todas las áreas';
        $parentAreaLabel = $selectedArea?->parent_name ?? ($selectedArea ? 'Sin area dependiente' : 'Todas las areas');
        $filenameBase = 'reporte-correos-'.$generatedAt->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('emails.report-pdf', compact('emailRequests', 'generatedAt', 'reportTitle', 'areaLabel', 'parentAreaLabel', 'search'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filenameBase.'.pdf');
        }

        abort_unless($format === 'excel', 404);

        $content = view('emails.report-excel', compact('emailRequests', 'generatedAt', 'reportTitle', 'areaLabel', 'parentAreaLabel', 'search'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.xls"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('emails.create'), 403);

        $data = $this->validatedData($request);

        $emailRequest = EmailRequest::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_cargo_id' => $data['email_cargo_id'],
            'email_movement_type_id' => $data['email_movement_type_id'],
            'created_by' => $request->user()->id,
        ]);

        $this->syncLink($emailRequest, $data['link'] ?? null, $request->user()->id);

        $content = "<p>Solicitud de correo registrada por {$request->user()->name}.</p>"
            ."<p><strong>Nombre:</strong> {$emailRequest->name}</p>"
            ."<p><strong>Correo:</strong> {$emailRequest->email}</p>"
            ."<p><strong>Cargo:</strong> {$emailRequest->cargo->name}</p>"
            ."<p><strong>Tipo de movimiento:</strong> {$emailRequest->movementType->name}</p>";

        if (! empty($data['link'])) {
            $content .= "<p><strong>Link:</strong> {$data['link']}</p>";
        }

        ChangeLogger::log($emailRequest, 'created', $content);

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo creada correctamente.');
    }

    public function update(Request $request, EmailRequest $emailRequest): RedirectResponse
    {
        abort_unless($request->user()->can('emails.update'), 403);

        $data = $this->validatedData($request);

        $originalName = $emailRequest->name;
        $originalEmail = $emailRequest->email;
        $originalCargo = $emailRequest->cargo?->name;
        $originalMovementType = $emailRequest->movementType->name;
        $originalLink = $emailRequest->links->first()?->url;

        $emailRequest->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_cargo_id' => $data['email_cargo_id'],
            'email_movement_type_id' => $data['email_movement_type_id'],
        ]);

        $emailRequest->load('cargo', 'movementType', 'links');
        $this->syncLink($emailRequest, $data['link'] ?? null, $request->user()->id);
        $emailRequest->load('links');

        $changes = [];

        if ($originalName !== $emailRequest->name) {
            $changes[] = "<p><strong>Nombre:</strong> {$originalName} ".self::CHANGE_ARROW." {$emailRequest->name}</p>";
        }

        if ($originalEmail !== $emailRequest->email) {
            $changes[] = "<p><strong>Correo:</strong> {$originalEmail} ".self::CHANGE_ARROW." {$emailRequest->email}</p>";
        }

        if ($originalCargo !== $emailRequest->cargo?->name) {
            $changes[] = '<p><strong>Cargo:</strong> '.($originalCargo ?: 'Sin cargo').' '.self::CHANGE_ARROW.' '.($emailRequest->cargo?->name ?: 'Sin cargo').'</p>';
        }

        if ($originalMovementType !== $emailRequest->movementType->name) {
            $changes[] = "<p><strong>Tipo de movimiento:</strong> {$originalMovementType} ".self::CHANGE_ARROW." {$emailRequest->movementType->name}</p>";
        }

        $newLink = $emailRequest->links->first()?->url;
        if ($originalLink !== $newLink) {
            $changes[] = '<p><strong>Link:</strong> '.($originalLink ?: 'Sin link').' '.self::CHANGE_ARROW.' '.($newLink ?: 'Sin link').'</p>';
        }

        if ($changes !== []) {
            ChangeLogger::log(
                $emailRequest,
                'updated',
                "<p>Solicitud de correo actualizada por {$request->user()->name}.</p>".implode('', $changes)
            );
        }

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo actualizada correctamente.');
    }

    public function destroy(Request $request, EmailRequest $emailRequest): RedirectResponse
    {
        abort_unless($request->user()->can('emails.delete'), 403);

        ChangeLogger::log(
            $emailRequest,
            'deleted',
            "<p>Solicitud de correo eliminada por {$request->user()->name}.</p>"
            ."<p><strong>Nombre:</strong> {$emailRequest->name}</p>"
            ."<p><strong>Correo:</strong> {$emailRequest->email}</p>"
            ."<p><strong>Cargo:</strong> ".($emailRequest->cargo?->name ?: 'Sin cargo')."</p>"
            ."<p><strong>Tipo de movimiento:</strong> {$emailRequest->movementType->name}</p>"
        );

        $emailRequest->links()->delete();
        $emailRequest->comments()->delete();
        $emailRequest->attachments()->delete();
        $emailRequest->changeLogs()->delete();
        $emailRequest->delete();

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo eliminada correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'email_cargo_id' => ['required', 'exists:email_cargos,id'],
            'email_movement_type_id' => ['required', 'exists:email_movement_types,id'],
            'link' => ['nullable', 'url', 'max:2048'],
        ]);
    }

    protected function syncLink(EmailRequest $emailRequest, ?string $url, int $userId): void
    {
        $currentLink = $emailRequest->links()->first();

        if (blank($url)) {
            if ($currentLink) {
                $currentLink->delete();
            }

            return;
        }

        if ($currentLink) {
            $currentLink->update([
                'label' => 'Solicitud de '.$emailRequest->name,
                'url' => $url,
            ]);

            return;
        }

        $emailRequest->links()->create([
            'label' => 'Solicitud de '.$emailRequest->name,
            'url' => $url,
            'created_by' => $userId,
        ]);
    }

    protected function emailsFilterContext(Request $request): array
    {
        $search = (string) $request->string('search');
        $selectedAreaId = $request->integer('area_id') ?: null;
        $cargos = EmailCargo::query()->orderBy('sort_order')->orderBy('name')->get();
        $selectedArea = $selectedAreaId ? $cargos->firstWhere('id', $selectedAreaId) : null;
        $areaOptions = $this->buildAreaOptions($cargos);

        return [$cargos, $search, $selectedAreaId, $selectedArea, $areaOptions];
    }

    protected function buildFilteredEmailRequestsQuery(Request $request, Collection $cargos): Builder
    {
        $search = (string) $request->string('search');
        $selectedAreaId = $request->integer('area_id') ?: null;
        $selectedArea = $selectedAreaId ? $cargos->firstWhere('id', $selectedAreaId) : null;
        $areaIds = $selectedArea ? $this->descendantAreaIds($selectedArea, $cargos) : [];

        return EmailRequest::query()
            ->with(['cargo', 'movementType', 'links', 'changeLogs.author'])
            ->when($selectedArea && $areaIds !== [], fn ($query) => $query->whereIn('email_cargo_id', $areaIds))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('cargo', fn ($cargoQuery) => $cargoQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('parent_name', 'like', "%{$search}%"))
                        ->orWhereHas('movementType', fn ($movementTypeQuery) => $movementTypeQuery->where('name', 'like', "%{$search}%"));
                });
            });
    }

    protected function buildAreaOptions(Collection $cargos): Collection
    {
        $groupedByParent = $cargos->groupBy(fn (EmailCargo $cargo) => $cargo->parent_name ?? '__root__');
        $options = collect();

        $appendOptions = function (string $parentName, int $depth) use (&$appendOptions, $groupedByParent, $options): void {
            foreach ($groupedByParent->get($parentName, collect()) as $cargo) {
                $options->push([
                    'id' => $cargo->id,
                    'label' => str_repeat('— ', $depth).$cargo->name,
                ]);

                $appendOptions($cargo->name, $depth + 1);
            }
        };

        $appendOptions('__root__', 0);

        return $options;
    }

    protected function descendantAreaIds(EmailCargo $selectedArea, Collection $cargos): array
    {
        $groupedByParent = $cargos->groupBy(fn (EmailCargo $cargo) => $cargo->parent_name ?? '__root__');
        $ids = [$selectedArea->id];

        $collectDescendants = function (string $parentName) use (&$collectDescendants, $groupedByParent, &$ids): void {
            foreach ($groupedByParent->get($parentName, collect()) as $cargo) {
                $ids[] = $cargo->id;
                $collectDescendants($cargo->name);
            }
        };

        $collectDescendants($selectedArea->name);

        return array_values(array_unique($ids));
    }
}