<?php

namespace App\Http\Controllers;

use App\Services\ChangeLogger;
use App\Support\ExcelXmlExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SystemRecord;
use App\Models\SystemStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class SystemRecordController extends Controller
{
    private const CHANGE_ARROW = '<span style="color:#2563eb;font-weight:700;">&rarr;</span>';

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('systems.view'), 403);

        $search = (string) $request->string('search');
        $selectedRequestDate = (string) $request->string('request_date');
        $selectedRequestYear = (string) $request->string('request_year');
        $selectedDateFrom = (string) $request->string('created_at_from');
        $selectedDateTo = (string) $request->string('created_at_to');

        $systems = $this->buildFilteredSystemsQuery($request)
            ->with(['links', 'attachments', 'changeLogs.author'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statuses = SystemStatus::query()->orderBy('name')->get();

        return view('systems.index', compact(
            'systems',
            'statuses',
            'search',
            'selectedRequestDate',
            'selectedRequestYear',
            'selectedDateFrom',
            'selectedDateTo',
        ));
    }

    public function report(Request $request, string $format): Response
    {
        abort_unless($request->user()->can('systems.view'), 403);

        $search = (string) $request->string('search');
        $selectedRequestDate = (string) $request->string('request_date');
        $selectedRequestYear = (string) $request->string('request_year');
        $selectedDateFrom = (string) $request->string('created_at_from');
        $selectedDateTo = (string) $request->string('created_at_to');

        $systems = $this->buildFilteredSystemsQuery($request)
            ->withCount('attachments')
            ->latest()
            ->get();

        $generatedAt = now();
        $reportTitle = 'Reporte de sistemas';
        $filenameBase = 'reporte-sistemas-'.$generatedAt->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('systems.report-pdf', compact(
                'systems',
                'generatedAt',
                'reportTitle',
                'search',
                'selectedRequestDate',
                'selectedRequestYear',
                'selectedDateFrom',
                'selectedDateTo',
            ))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filenameBase.'.pdf');
        }

        abort_unless($format === 'excel', 404);

        return ExcelXmlExporter::download(
            $filenameBase,
            'Reporte de sistemas',
            $this->buildSystemReportMetadata(
                $generatedAt->format('d/m/Y'),
                $search,
                $selectedRequestDate,
                $selectedRequestYear,
                $selectedDateFrom,
                $selectedDateTo,
            ),
            ['No.', 'Nombre del sistema', 'Fecha de solicitud', 'Fecha de creación', 'Estatus'],
            $this->buildSystemReportRows($systems),
        );
    }

    public function historyReport(Request $request, SystemRecord $system, string $format): Response
    {
        abort_unless($request->user()->can('systems.view'), 403);

        $system->loadMissing(['status', 'changeLogs.author']);

        $generatedAt = now();
        $reportTitle = 'Reporte de historial de sistema';
        $filenameBase = 'historial-sistema-'.str($system->name)->slug()->toString().'-'.$generatedAt->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('systems.history-report-pdf', compact('system', 'generatedAt', 'reportTitle'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filenameBase.'.pdf');
        }

        abort_unless($format === 'excel', 404);

        return ExcelXmlExporter::download(
            $filenameBase,
            'Historial de sistema',
            [
                ['Fecha de generación:', $generatedAt->format('d/m/Y')],
                ['Fecha de solicitud:', $system->request_date?->format('d/m/Y') ?? 'Sin fecha'],
                ['Nombre del sistema:', $system->name],
                ['Estatus actual:', $system->status?->display_name ?? 'Sin estatus'],
            ],
            ['No.', 'Fecha', 'Acción', 'Autor', 'Estatus', 'Detalle'],
            $this->buildSystemHistoryRows($system),
        );
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('systems.create'), 403);

        $data = $this->validatedData($request);

        $system = SystemRecord::query()->create([
            'request_date' => $data['request_date'],
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
        $uploadedAttachments = $this->syncAttachments($system, $request->file('attachments', []), $request->user()->id);
        $system->load(['status', 'links']);

        ChangeLogger::log(
            $system,
            'created',
            $this->wrapHistoryContent(
                $system->status?->name,
                $this->buildCreatedSystemLogContent($system, $request->user()->name, $uploadedAttachments)
            )
        );

        return redirect()->route('systems.index')->with('status', 'Sistema creado correctamente.');
    }


        protected function buildSystemReportMetadata(
            string $generatedAt,
            string $search,
            string $selectedRequestDate,
            string $selectedRequestYear,
            string $selectedDateFrom,
            string $selectedDateTo,
        ): array {
            return [
                ['Fecha de generación', $generatedAt],
                ['Búsqueda aplicada', $search],
                ['Fecha de solicitud', $selectedRequestDate !== '' ? $this->formatDateLabel($selectedRequestDate) : ''],
                ['Año de solicitud', $selectedRequestYear],
                ['Fecha de creación', ($selectedDateFrom !== '' || $selectedDateTo !== '')
                    ? ($selectedDateFrom !== '' ? $this->formatDateLabel($selectedDateFrom) : 'Sin inicio')
                        .' - '
                        .($selectedDateTo !== '' ? $this->formatDateLabel($selectedDateTo) : 'Sin fin')
                    : ''],
            ];
        }

        protected function buildSystemReportRows(Collection $systems): array
        {
            return $systems->values()->map(fn (SystemRecord $system, int $index): array => [
                $index + 1,
                $system->name,
                $system->request_date?->format('d/m/Y') ?? 'Sin fecha',
                $system->created_at->format('d/m/Y'),
                $this->buildSystemStatusCell($system),
            ])->all();
        }

        protected function buildSystemHistoryRows(SystemRecord $system): array
        {
            return $system->changeLogs->values()->map(function ($log, int $index): array {
                $reportContent = preg_replace(
                    '/<p>Sistema (?:actualizado|registrado|eliminado) por .*?<\/p>/is',
                    '',
                    $log->rendered_content,
                    1,
                ) ?? $log->rendered_content;

                return [
                    $index + 1,
                    $log->created_at->format('d/m/Y'),
                    $log->localized_action,
                    optional($log->author)->name ?? 'Sistema',
                    $log->status_group,
                    $this->formatSystemHistoryDetailForExcel($reportContent),
                ];
            })->all();
        }

        protected function formatSystemHistoryDetailForExcel(string $content): string
        {
            $contentWithLinks = preg_replace_callback(
                '/<a\s+[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/isu',
                static function (array $matches): string {
                    $url = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $label = trim(strip_tags(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8')));

                    if ($label === '') {
                        return $url;
                    }

                    return e($label.': '.$url);
                },
                $content,
            ) ?? $content;

            $contentWithoutDuplicatedUrls = preg_replace(
                '/\s*<span[^>]*>\((?:https?:\/\/|www\.)[^<]+\)<\/span>/iu',
                '',
                $contentWithLinks,
            ) ?? $contentWithLinks;

            return ExcelXmlExporter::plainText($contentWithoutDuplicatedUrls);
        }

        protected function buildSystemStatusCell(SystemRecord $system): string
        {
            $status = $system->status?->display_name ?? 'Sin estatus';

            if (! $system->status?->isTesting()) {
                return $status;
            }

            return $status."\n"
                .'Tarjetas errores pendientes: '.($system->pending_errors ?? 0)."\n"
                .'Tarjetas errores en proceso de solución: '.($system->errors_in_progress ?? 0)."\n"
                .'Tarjetas en revisión: '.($system->in_review ?? 0)."\n"
                .'Tarjetas finalizadas: '.($system->finalized ?? 0)."\n"
                .'Total de tarjetas en trello: '.$system->total_trello_cards;
        }
    public function update(Request $request, SystemRecord $system): RedirectResponse
    {
        abort_unless($request->user()->can('systems.update'), 403);

        $data = $this->validatedData($request);
        $originalRequestDate = $system->request_date?->format('Y-m-d');
        $originalName = $system->name;
        $originalTrelloUrl = $system->trello_url;
        $originalStatusId = $system->system_status_id;
        $originalStatusName = $system->status?->display_name ?? 'Sin estatus';
        $originalLink = $system->links->first()?->url;
        $originalPendingErrors = $system->pending_errors;
        $originalErrorsInProgress = $system->errors_in_progress;
        $originalInReview = $system->in_review;
        $originalFinalized = $system->finalized;
        $originalTotalTrelloCards = $this->calculateTotalTrelloCards(
            $originalPendingErrors,
            $originalErrorsInProgress,
            $originalInReview,
            $originalFinalized,
        );

        $system->update([
            'request_date' => $data['request_date'],
            'name' => $data['name'],
            'trello_url' => $data['trello_url'] ?? null,
            'system_status_id' => $data['system_status_id'],
            'pending_errors' => $data['pending_errors'],
            'errors_in_progress' => $data['errors_in_progress'],
            'in_review' => $data['in_review'],
            'finalized' => $data['finalized'],
        ]);

        $this->syncLink($system, $data['link'] ?? null, $request->user()->id);
        $uploadedAttachments = $this->syncAttachments($system, $request->file('attachments', []), $request->user()->id);
        $system->load(['status', 'links']);

        $changes = [];

        $updatedRequestDate = $system->request_date?->format('Y-m-d');
        if ($originalRequestDate !== $updatedRequestDate) {
            $changes[] = '<p><strong>Fecha de solicitud:</strong> '.e($this->formatRequestDate($originalRequestDate)).' '.self::CHANGE_ARROW.' '.e($this->formatRequestDate($updatedRequestDate)).'</p>';
        }

        if ($originalName !== $system->name) {
            $changes[] = '<p><strong>Nombre del sistema:</strong> '.e($originalName).' '.self::CHANGE_ARROW.' '.e($system->name).'</p>';
        }

        $updatedLink = $system->links->first()?->url;
        if ($originalLink !== $updatedLink) {
            $changes[] = '<p><strong>Link:</strong> '.$this->renderHistoryUrl($originalLink).' '.self::CHANGE_ARROW.' '.$this->renderHistoryUrl($updatedLink).'</p>';
        }

        if ($originalTrelloUrl !== $system->trello_url) {
            $changes[] = '<p><strong>Trello:</strong> '.$this->renderHistoryUrl($originalTrelloUrl, 'Abrir Trello', 'Sin Trello').' '.self::CHANGE_ARROW.' '.$this->renderHistoryUrl($system->trello_url, 'Abrir Trello', 'Sin Trello').'</p>';
        }

        $updatedStatusName = $system->status?->display_name ?? 'Sin estatus';
        $statusChanged = $originalStatusName !== $updatedStatusName;

        if ($statusChanged) {
            $changes[] = '<p><strong>Estatus:</strong> '.e($originalStatusName).' '.self::CHANGE_ARROW.' '.e($updatedStatusName).'</p>';
        }

        $this->appendMetricChange($changes, 'Tarjetas errores pendientes', $originalPendingErrors, $system->pending_errors);
        $this->appendMetricChange($changes, 'Tarjetas errores en proceso de solución', $originalErrorsInProgress, $system->errors_in_progress);
        $this->appendMetricChange($changes, 'Tarjetas en revisión', $originalInReview, $system->in_review);
        $this->appendMetricChange($changes, 'Tarjetas finalizadas', $originalFinalized, $system->finalized);
        $this->appendMetricChange($changes, 'Total de tarjetas en trello', $originalTotalTrelloCards, $system->total_trello_cards);

        if ($uploadedAttachments !== []) {
            $changes[] = $this->renderAttachmentList('Adjuntos agregados', $uploadedAttachments, true);
        }

        if ($changes !== []) {
            ChangeLogger::log(
                $system,
                $statusChanged ? 'status_changed' : 'updated',
                $this->wrapHistoryContent(
                    $system->status?->name,
                    '<p>Sistema actualizado por '.e($request->user()->name).'.</p>'.implode('', $changes)
                )
            );
        }

        return redirect()->route('systems.index')->with('status', 'Sistema actualizado correctamente.');
    }

    public function destroy(Request $request, SystemRecord $system): RedirectResponse
    {
        abort_unless($request->user()->can('systems.delete'), 403);

        $system->load(['status', 'links', 'attachments']);

        ChangeLogger::log(
            $system,
            'deleted',
            $this->wrapHistoryContent(
                $system->status?->name,
                $this->buildDeletedSystemLogContent($system, $request->user()->name)
            )
        );

        $system->delete();

        return redirect()->route('systems.index')->with('status', 'Sistema eliminado correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'request_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'system_status_id' => ['required', 'exists:system_statuses,id'],
            'link' => ['nullable', 'url', 'max:5000'],
            'trello_url' => ['nullable', 'url', 'max:5000'],
            'pending_errors' => ['nullable', 'integer', 'min:0'],
            'errors_in_progress' => ['nullable', 'integer', 'min:0'],
            'in_review' => ['nullable', 'integer', 'min:0'],
            'finalized' => ['nullable', 'integer', 'min:0'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $status = SystemStatus::query()->find($data['system_status_id']);

        if ($status?->isTesting()) {
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
        $requestDate = (string) $request->string('request_date');
        $requestYear = (string) $request->string('request_year');
        $createdAtFrom = (string) $request->string('created_at_from');
        $createdAtTo = (string) $request->string('created_at_to');

        return SystemRecord::query()
            ->with('status')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('status', fn ($statusQuery) => $statusQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($requestDate !== '', fn ($query) => $query->whereDate('request_date', $requestDate))
            ->when($requestYear !== '', fn ($query) => $query->whereYear('request_date', (int) $requestYear))
            ->when($createdAtFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $createdAtFrom))
            ->when($createdAtTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $createdAtTo));
    }

    protected function syncAttachments(SystemRecord $system, array $files, int $userId): array
    {
        $uploadedAttachments = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('attachments/system', 'public');

            $attachment = $system->attachments()->create([
                'uploaded_by' => $userId,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $uploadedAttachments[] = [
                'name' => $attachment->original_name,
                'url' => route('attachments.show', $attachment),
            ];
        }

        return $uploadedAttachments;
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

    protected function appendMetricChange(array &$changes, string $label, mixed $originalValue, mixed $updatedValue): void
    {
        $originalValue = $originalValue ?? 0;
        $updatedValue = $updatedValue ?? 0;

        if ((int) $originalValue !== (int) $updatedValue) {
            $changes[] = '<p><strong>'.e($label).':</strong> '.e((string) $originalValue).' '.self::CHANGE_ARROW.' '.e((string) $updatedValue).'</p>';
        }
    }

    protected function calculateTotalTrelloCards(mixed $pendingErrors, mixed $errorsInProgress, mixed $inReview, mixed $finalized): int
    {
        return (int) ($pendingErrors ?? 0)
            + (int) ($errorsInProgress ?? 0)
            + (int) ($inReview ?? 0)
            + (int) ($finalized ?? 0);
    }

    protected function buildCreatedSystemLogContent(SystemRecord $system, string $authorName, array $uploadedAttachments): string
    {
        $content = '<p>Sistema registrado por '.e($authorName).'.</p>'
            .'<p><strong>Fecha de solicitud:</strong> '.e($system->request_date?->format('d/m/Y') ?? 'Sin fecha').'</p>'
            .'<p><strong>Nombre del sistema:</strong> '.e($system->name).'</p>'
            .'<p><strong>Estatus:</strong> '.e($system->status?->display_name ?? 'Sin estatus').'</p>'
            .'<p><strong>Link:</strong> '.$this->renderHistoryUrl($system->links->first()?->url).'</p>'
            .'<p><strong>Trello:</strong> '.$this->renderHistoryUrl($system->trello_url, 'Abrir Trello', 'Sin Trello').'</p>';

        $content .= $this->renderTestingMetricsSnapshot($system);

        if ($uploadedAttachments !== []) {
            $content .= $this->renderAttachmentList('Adjuntos agregados', $uploadedAttachments, true);
        }

        return $content;
    }

    protected function buildDeletedSystemLogContent(SystemRecord $system, string $authorName): string
    {
        $content = '<p>Sistema eliminado por '.e($authorName).'.</p>'
            .'<p><strong>Fecha de solicitud:</strong> '.e($system->request_date?->format('d/m/Y') ?? 'Sin fecha').'</p>'
            .'<p><strong>Nombre del sistema:</strong> '.e($system->name).'</p>'
            .'<p><strong>Estatus:</strong> '.e($system->status?->display_name ?? 'Sin estatus').'</p>'
            .'<p><strong>Link:</strong> '.$this->renderHistoryUrl($system->links->first()?->url).'</p>'
            .'<p><strong>Trello:</strong> '.$this->renderHistoryUrl($system->trello_url, 'Abrir Trello', 'Sin Trello').'</p>';

        $content .= $this->renderTestingMetricsSnapshot($system);

        if ($system->attachments->isNotEmpty()) {
            $content .= $this->renderAttachmentList('Adjuntos existentes al eliminar', $system->attachments->pluck('original_name')->all());
        }

        return $content;
    }

    protected function renderTestingMetricsSnapshot(SystemRecord $system): string
    {
        if (! $system->status?->isTesting()) {
            return '';
        }

        return '<p><strong>Tarjetas errores pendientes:</strong> '.e((string) ($system->pending_errors ?? 0)).'</p>'
            .'<p><strong>Tarjetas errores en proceso de solución:</strong> '.e((string) ($system->errors_in_progress ?? 0)).'</p>'
            .'<p><strong>Tarjetas en revisión:</strong> '.e((string) ($system->in_review ?? 0)).'</p>'
            .'<p><strong>Tarjetas finalizadas:</strong> '.e((string) ($system->finalized ?? 0)).'</p>'
            .'<p><strong>Total de tarjetas en trello:</strong> '.e((string) $system->total_trello_cards).'</p>';
    }

    protected function renderAttachmentList(string $title, array $attachments, bool $includeLinks = false): string
    {
        $items = collect($attachments)
            ->filter()
            ->map(function (mixed $attachment) use ($includeLinks) {
                if (is_array($attachment)) {
                    $name = $attachment['name'] ?? '';
                    $url = $attachment['url'] ?? null;

                    if ($name === '') {
                        return null;
                    }

                    if ($includeLinks && filled($url)) {
                        return '<li>'.e($name).' <a href="'.e($url).'" target="_blank" style="color:#960018;text-decoration:underline;">Abrir archivo</a></li>';
                    }

                    return '<li>'.e($name).'</li>';
                }

                if (! is_string($attachment) || $attachment === '') {
                    return null;
                }

                return '<li>'.e($attachment).'</li>';
            })
            ->filter()
            ->implode('');

        if ($items === '') {
            return '';
        }

        return '<div><strong>'.e($title).':</strong><ul style="margin:6px 0 0 18px;list-style:disc;">'.$items.'</ul></div>';
    }

    protected function renderHistoryUrl(?string $url, string $label = 'Abrir link', string $emptyLabel = 'Sin link'): string
    {
        if (blank($url)) {
            return $emptyLabel;
        }

        return '<a href="'.e($url).'" target="_blank" style="color:#960018;text-decoration:underline;">'.e($label).'</a>'
            .' <span style="color:#64748b;word-break:break-all;">('.e($url).')</span>';
    }

    protected function formatRequestDate(?string $date): string
    {
        if (blank($date)) {
            return 'Sin fecha';
        }

        return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
    }

    protected function formatDateLabel(string $date): string
    {
        return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
    }

    protected function wrapHistoryContent(?string $statusName, string $content): string
    {
        return '<div data-status-group="'.e($statusName ?: 'Sin estatus').'">'.$content.'</div>';
    }
}