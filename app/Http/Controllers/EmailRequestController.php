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

        [$cargos, $search, $selectedAreaId, $selectedArea, $selectedMovementTypeId, $selectedMovementType, $selectedStatus, $statusLabel, $selectedDateFrom, $selectedDateTo, $dateLabel, $selectedRequestDate, $requestDateLabel, $selectedRequestYear, $requestYearLabel, $areaOptions] = $this->emailsFilterContext($request);

        $emailRequests = $this->buildFilteredEmailRequestsQuery($request, $cargos)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $movementTypes = EmailMovementType::query()->orderBy('name')->get();

        return view('emails.index', compact(
            'emailRequests',
            'cargos',
            'movementTypes',
            'search',
            'selectedAreaId',
            'selectedArea',
            'selectedMovementTypeId',
            'selectedMovementType',
            'selectedStatus',
            'statusLabel',
            'selectedDateFrom',
            'selectedDateTo',
            'dateLabel',
            'selectedRequestDate',
            'requestDateLabel',
            'selectedRequestYear',
            'requestYearLabel',
            'areaOptions'
        ));
    }

    public function report(Request $request, string $format): Response
    {
        abort_unless($request->user()->can('emails.view'), 403);

        [$cargos, $search, $selectedAreaId, $selectedArea, $selectedMovementTypeId, $selectedMovementType, $selectedStatus, $statusLabel, $selectedDateFrom, $selectedDateTo, $dateLabel, $selectedRequestDate, $requestDateLabel, $selectedRequestYear, $requestYearLabel] = $this->emailsFilterContext($request);

        $emailRequests = $this->buildFilteredEmailRequestsQuery($request, $cargos)
            ->latest()
            ->get();

        $generatedAt = now();
        $reportTitle = 'Reporte de correos';
        $areaLabel = $selectedArea?->name ?? 'Todas las áreas';
        $parentAreaLabel = $selectedArea?->parent_name ?? ($selectedArea ? 'Sin superior' : 'Todas las areas');
        $movementTypeLabel = $selectedMovementType?->name ?? 'Todos los movimientos';
        $filenameBase = 'reporte-correos-'.$generatedAt->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('emails.report-pdf', compact('emailRequests', 'generatedAt', 'reportTitle', 'areaLabel', 'parentAreaLabel', 'movementTypeLabel', 'selectedStatus', 'statusLabel', 'dateLabel', 'selectedRequestDate', 'requestDateLabel', 'selectedRequestYear', 'requestYearLabel', 'search'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filenameBase.'.pdf');
        }

        abort_unless($format === 'excel', 404);

        $content = view('emails.report-excel', compact('emailRequests', 'generatedAt', 'reportTitle', 'areaLabel', 'parentAreaLabel', 'movementTypeLabel', 'selectedStatus', 'statusLabel', 'dateLabel', 'selectedRequestDate', 'requestDateLabel', 'selectedRequestYear', 'requestYearLabel', 'search'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.xls"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function historyReport(Request $request, EmailRequest $emailRequest, string $format): Response
    {
        abort_unless($request->user()->can('emails.view'), 403);

        $emailRequest->loadMissing(['cargo', 'movementType', 'changeLogs.author']);

        $generatedAt = now();
        $reportTitle = 'Reporte de historial de correo';
        $filenameBase = 'historial-correo-'.str($emailRequest->name)->slug()->toString().'-'.$generatedAt->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('emails.history-report-pdf', compact('emailRequest', 'generatedAt', 'reportTitle'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filenameBase.'.pdf');
        }

        abort_unless($format === 'excel', 404);

        $content = view('emails.history-report-excel', compact('emailRequest', 'generatedAt', 'reportTitle'))->render();

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
            'request_date' => $data['request_date'],
            'name' => $data['name'],
            'email' => $data['email'],
            'email_cargo_id' => $data['email_cargo_id'],
            'email_movement_type_id' => $data['email_movement_type_id'],
            'created_by' => $request->user()->id,
        ]);

        $this->syncLink($emailRequest, $data['link'] ?? null, $request->user()->id);

        $content = '<p>Solicitud de correo registrada por '.e($request->user()->name).'.</p>'
            .'<p><strong>Fecha de solicitud:</strong> '.e($emailRequest->request_date?->format('d/m/Y') ?? 'Sin fecha').'</p>'
            .'<p><strong>Nombre:</strong> '.e($emailRequest->name).'</p>'
            .'<p><strong>Correo:</strong> '.e($emailRequest->email).'</p>'
            .'<p><strong>Cargo:</strong> '.e($emailRequest->cargo->name).'</p>'
            .'<p><strong>Tipo de movimiento:</strong> '.e($emailRequest->movementType->name).'</p>';

        if (! empty($data['link'])) {
            $content .= '<p><strong>Link:</strong> '.$this->renderHistoryUrl($data['link']).'</p>';
        }

        ChangeLogger::log($emailRequest, 'created', $content);

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo creada correctamente.');
    }

    public function update(Request $request, EmailRequest $emailRequest): RedirectResponse
    {
        abort_unless($request->user()->can('emails.update'), 403);

        $data = $this->validatedData($request);

        $originalRequestDate = $emailRequest->request_date?->format('Y-m-d');
        $originalName = $emailRequest->name;
        $originalEmail = $emailRequest->email;
        $originalCargo = $emailRequest->cargo?->name;
        $originalMovementType = $emailRequest->movementType->name;
        $originalLink = $emailRequest->links->first()?->url;

        $emailRequest->update([
            'request_date' => $data['request_date'],
            'name' => $data['name'],
            'email' => $data['email'],
            'email_cargo_id' => $data['email_cargo_id'],
            'email_movement_type_id' => $data['email_movement_type_id'],
        ]);

        $emailRequest->load('cargo', 'movementType', 'links');
        $this->syncLink($emailRequest, $data['link'] ?? null, $request->user()->id);
        $emailRequest->load('links');

        $changes = [];

        $updatedRequestDate = $emailRequest->request_date?->format('Y-m-d');
        if ($originalRequestDate !== $updatedRequestDate) {
            $changes[] = '<p><strong>Fecha de solicitud:</strong> '.e($this->formatRequestDate($originalRequestDate)).' '.self::CHANGE_ARROW.' '.e($this->formatRequestDate($updatedRequestDate)).'</p>';
        }

        if ($originalName !== $emailRequest->name) {
            $changes[] = '<p><strong>Nombre:</strong> '.e($originalName).' '.self::CHANGE_ARROW.' '.e($emailRequest->name).'</p>';
        }

        if ($originalEmail !== $emailRequest->email) {
            $changes[] = '<p><strong>Correo:</strong> '.e($originalEmail).' '.self::CHANGE_ARROW.' '.e($emailRequest->email).'</p>';
        }

        if ($originalCargo !== $emailRequest->cargo?->name) {
            $changes[] = '<p><strong>Cargo:</strong> '.($originalCargo ?: 'Sin cargo').' '.self::CHANGE_ARROW.' '.($emailRequest->cargo?->name ?: 'Sin cargo').'</p>';
        }

        if ($originalMovementType !== $emailRequest->movementType->name) {
            $changes[] = '<p><strong>Tipo de movimiento:</strong> '.e($originalMovementType).' '.self::CHANGE_ARROW.' '.e($emailRequest->movementType->name).'</p>';
        }

        $newLink = $emailRequest->links->first()?->url;
        if ($originalLink !== $newLink) {
            $changes[] = '<p><strong>Link:</strong> '.$this->renderHistoryUrl($originalLink).' '.self::CHANGE_ARROW.' '.$this->renderHistoryUrl($newLink).'</p>';
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
            ."<p><strong>Fecha de solicitud:</strong> ".e($emailRequest->request_date?->format('d/m/Y') ?? 'Sin fecha')."</p>"
            ."<p><strong>Nombre:</strong> {$emailRequest->name}</p>"
            ."<p><strong>Correo:</strong> {$emailRequest->email}</p>"
            ."<p><strong>Cargo:</strong> ".($emailRequest->cargo?->name ?: 'Sin cargo')."</p>"
            ."<p><strong>Tipo de movimiento:</strong> {$emailRequest->movementType->name}</p>"
        );

        $emailRequest->delete();

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo eliminada correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'request_date' => ['required', 'date'],
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

    protected function renderHistoryUrl(?string $url): string
    {
        if (blank($url)) {
            return 'Sin link';
        }

        return '<a href="'.e($url).'" target="_blank" style="color:#960018;text-decoration:underline;">Abrir link</a>'
            .' <span style="color:#64748b;word-break:break-all;">('.e($url).')</span>';
    }

    protected function formatRequestDate(?string $date): string
    {
        if (blank($date)) {
            return 'Sin fecha';
        }

        return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
    }

    protected function emailsFilterContext(Request $request): array
    {
        $search = (string) $request->string('search');
        $selectedAreaId = $request->integer('area_id') ?: null;
        $selectedMovementTypeId = $request->integer('movement_type_id') ?: null;
        $selectedStatus = trim($request->string('status')->toString());
        $selectedRequestDate = trim($request->string('request_date')->toString());
        $selectedRequestYear = trim($request->string('request_year')->toString());
        [$selectedDateFrom, $selectedDateTo] = $this->normalizedDateRange(
            $request->string('created_at_from')->toString(),
            $request->string('created_at_to')->toString(),
            $request->string('created_at')->toString()
        );
        $cargos = EmailCargo::query()->orderBy('sort_order')->orderBy('name')->get();
        $selectedArea = $selectedAreaId ? $cargos->firstWhere('id', $selectedAreaId) : null;
        $selectedMovementType = $selectedMovementTypeId
            ? EmailMovementType::query()->find($selectedMovementTypeId)
            : null;
        $statusLabel = match ($selectedStatus) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            default => 'Todos los estatus',
        };
        $dateLabel = $this->buildDateRangeLabel($selectedDateFrom, $selectedDateTo);
        $requestDateLabel = $selectedRequestDate !== '' ? $this->formatDateLabel($selectedRequestDate) : 'Todas las fechas de solicitud';
        $requestYearLabel = $selectedRequestYear !== '' ? $selectedRequestYear : 'Todos los años de solicitud';
        $areaOptions = $this->buildAreaOptions($cargos);

        return [$cargos, $search, $selectedAreaId, $selectedArea, $selectedMovementTypeId, $selectedMovementType, $selectedStatus, $statusLabel, $selectedDateFrom, $selectedDateTo, $dateLabel, $selectedRequestDate, $requestDateLabel, $selectedRequestYear, $requestYearLabel, $areaOptions];
    }

    protected function buildFilteredEmailRequestsQuery(Request $request, Collection $cargos): Builder
    {
        $search = (string) $request->string('search');
        $selectedAreaId = $request->integer('area_id') ?: null;
        $selectedMovementTypeId = $request->integer('movement_type_id') ?: null;
        $selectedStatus = trim($request->string('status')->toString());
        $selectedRequestDate = trim($request->string('request_date')->toString());
        $selectedRequestYear = trim($request->string('request_year')->toString());
        [$selectedDateFrom, $selectedDateTo] = $this->normalizedDateRange(
            $request->string('created_at_from')->toString(),
            $request->string('created_at_to')->toString(),
            $request->string('created_at')->toString()
        );
        $selectedArea = $selectedAreaId ? $cargos->firstWhere('id', $selectedAreaId) : null;
        $areaIds = $selectedArea ? $this->descendantAreaIds($selectedArea, $cargos) : [];

        return EmailRequest::query()
            ->with(['cargo', 'movementType', 'links', 'changeLogs.author'])
            ->when($selectedArea && $areaIds !== [], fn ($query) => $query->whereIn('email_cargo_id', $areaIds))
            ->when($selectedMovementTypeId, fn ($query) => $query->where('email_movement_type_id', $selectedMovementTypeId))
            ->when($selectedStatus === 'active', fn ($query) => $query->whereHas('movementType', fn ($movementTypeQuery) => $movementTypeQuery->whereIn('slug', ['alta', 'cambio-de-contrasena'])))
            ->when($selectedStatus === 'inactive', fn ($query) => $query->whereHas('movementType', fn ($movementTypeQuery) => $movementTypeQuery->where('slug', 'baja')))
            ->when($selectedRequestDate !== '', fn ($query) => $query->whereDate('request_date', $selectedRequestDate))
            ->when($selectedRequestYear !== '', fn ($query) => $query->whereYear('request_date', (int) $selectedRequestYear))
            ->when($selectedDateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $selectedDateFrom))
            ->when($selectedDateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $selectedDateTo))
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

    protected function normalizedDateRange(?string $dateFrom, ?string $dateTo, ?string $fallbackDate = null): array
    {
        $dateFrom = trim((string) $dateFrom);
        $dateTo = trim((string) $dateTo);
        $fallbackDate = trim((string) $fallbackDate);

        if ($dateFrom === '' && $dateTo === '' && $fallbackDate !== '') {
            return [$fallbackDate, $fallbackDate];
        }

        if ($dateFrom !== '' && $dateTo === '') {
            return [$dateFrom, $dateFrom];
        }

        if ($dateFrom === '' && $dateTo !== '') {
            return [$dateTo, $dateTo];
        }

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            return [$dateTo, $dateFrom];
        }

        return [$dateFrom, $dateTo];
    }

    protected function buildDateRangeLabel(?string $dateFrom, ?string $dateTo): string
    {
        $dateFrom = trim((string) $dateFrom);
        $dateTo = trim((string) $dateTo);
        $formattedDateFrom = $dateFrom !== '' ? $this->formatDateLabel($dateFrom) : '';
        $formattedDateTo = $dateTo !== '' ? $this->formatDateLabel($dateTo) : '';

        if ($dateFrom !== '' && $dateTo !== '') {
            return $dateFrom === $dateTo
                ? $formattedDateFrom
                : $formattedDateFrom.' a '.$formattedDateTo;
        }

        if ($dateFrom !== '') {
            return 'Desde '.$formattedDateFrom;
        }

        if ($dateTo !== '') {
            return 'Hasta '.$formattedDateTo;
        }

        return 'Todas las fechas';
    }

    protected function formatDateLabel(string $date): string
    {
        return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
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