@php
    $testingStatusIds = $statuses
        ->filter(fn ($status) => $status->isTesting())
        ->pluck('id')
        ->map(fn ($id) => (string) $id)
        ->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Sistemas</h2>
                <p class="text-sm text-white/80">Registro de movimientos relacionados a sistemas.</p>
            </div>
            @can('systems.create')
                <button class="app-button-light" type="button" x-data @click="$dispatch('open-modal', 'create-system-record')">
                    Agregar
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="app-card p-4">
                <ul class="space-y-1 text-sm text-rose-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" class="app-card relative p-4" x-data="filterDrawer()" @keydown.escape.window="close()">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="flex-1">
                    <input
                        name="search"
                        value="{{ $search ?? '' }}"
                        class="app-input mt-0"
                        placeholder="Buscar por nombre del sistema o estatus..."
                        @input="if ($event.target.value.trim() === '' && @js(($search ?? '') !== '')) { $el.form.requestSubmit(); }"
                    >
                </div>

                <div class="flex gap-3">
                    <button type="button" class="app-button-secondary" @click="show()" :aria-expanded="open.toString()">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.5 4.75A1.25 1.25 0 0 1 3.75 3.5h12.5a1.25 1.25 0 0 1 .97 2.04L12 11.95v3.55a1.25 1.25 0 0 1-.61 1.07l-2 1.2A1.25 1.25 0 0 1 7.5 16.7v-4.75L2.78 5.54a1.25 1.25 0 0 1-.28-.79Z" clip-rule="evenodd" />
                        </svg>
                        Filtros
                    </button>
                    <button class="app-button" style="color: #ffffff !important;" type="submit">Buscar</button>
                </div>
            </div>

            <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[140] bg-slate-950/30 backdrop-blur-sm" @click="close()"></div>

            <div
                x-cloak
                x-show="open"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="fixed inset-y-0 right-0 z-[150] w-full max-w-xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl shadow-[#960018]/20"
            >
                <div class="sticky top-0 border-b border-slate-200 bg-white/95 px-6 py-5 backdrop-blur">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Filtros de sistemas</h3>
                            <p class="mt-1 text-sm text-slate-500">Ajusta la búsqueda desde este panel lateral.</p>
                        </div>
                        <button type="button" class="rounded-2xl px-3 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" @click="close()">Cerrar</button>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-6">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="system-request-date-filter" class="app-label">Fecha de solicitud</label>
                        <input id="system-request-date-filter" name="request_date" type="date" class="app-input" value="{{ $selectedRequestDate ?? '' }}">
                        <p class="mt-2 text-xs text-slate-500">Busca los registros usando la fecha de solicitud del sistema.</p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="system-request-year-filter" class="app-label">Año de solicitud</label>
                        <input id="system-request-year-filter" name="request_year" type="number" min="2000" max="2100" class="app-input" value="{{ $selectedRequestYear ?? '' }}" placeholder="2026">
                        <p class="mt-2 text-xs text-slate-500">Filtra por el año capturado en la fecha de solicitud.</p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="system-created-at-from" class="app-label">Fecha desde</label>
                        <input id="system-created-at-from" name="created_at_from" type="date" class="app-input" value="{{ $selectedDateFrom ?? '' }}">
                        <p class="mt-2 text-xs text-slate-500">Si eliges sólo esta fecha, se buscarán los registros creados desde ese día.</p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="system-created-at-to" class="app-label">Fecha hasta</label>
                        <input id="system-created-at-to" name="created_at_to" type="date" class="app-input" value="{{ $selectedDateTo ?? '' }}">
                        <p class="mt-2 text-xs text-slate-500">Úsala junto con Fecha desde para acotar el rango.</p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-between">
                        <a href="{{ route('systems.index') }}" class="app-button-secondary justify-center">Limpiar</a>
                        <button type="submit" class="app-button justify-center" style="color: #ffffff !important;" @click="showFilters = false">Aplicar filtros</button>
                    </div>
                </div>
            </div>

            @if (($selectedRequestDate ?? '') !== '' || ($selectedRequestYear ?? '') !== '' || ($selectedDateFrom ?? '') !== '' || ($selectedDateTo ?? '') !== '' || ($search ?? '') !== '')
                <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
                    @if (($selectedRequestDate ?? '') !== '')
                        <span>Fecha de solicitud: <span class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($selectedRequestDate)->format('d/m/Y') }}</span></span>
                    @endif
                    @if (($selectedRequestYear ?? '') !== '')
                        <span>Año de solicitud: <span class="font-semibold text-slate-900">{{ $selectedRequestYear }}</span></span>
                    @endif
                    @if (($selectedDateFrom ?? '') !== '' || ($selectedDateTo ?? '') !== '')
                        <span>Fecha de creación: <span class="font-semibold text-slate-900">{{ ($selectedDateFrom ?? '') !== '' ? \Carbon\Carbon::parse($selectedDateFrom)->format('d/m/Y') : 'Sin inicio' }} - {{ ($selectedDateTo ?? '') !== '' ? \Carbon\Carbon::parse($selectedDateTo)->format('d/m/Y') : 'Sin fin' }}</span></span>
                    @endif
                    @if (($search ?? '') !== '')
                        <span>Búsqueda: <span class="font-semibold text-slate-900">{{ $search }}</span></span>
                    @endif
                </div>
            @endif
        </form>

        <div class="flex justify-start gap-3">
            <a href="{{ route('systems.report', ['format' => 'excel', 'search' => $search ?? '', 'request_date' => $selectedRequestDate ?? '', 'request_year' => $selectedRequestYear ?? '', 'created_at_from' => $selectedDateFrom ?? '', 'created_at_to' => $selectedDateTo ?? '']) }}" class="app-button-secondary">Descargar Excel</a>
            <a href="{{ route('systems.report', ['format' => 'pdf', 'search' => $search ?? '', 'request_date' => $selectedRequestDate ?? '', 'request_year' => $selectedRequestYear ?? '', 'created_at_from' => $selectedDateFrom ?? '', 'created_at_to' => $selectedDateTo ?? '']) }}" class="app-button-secondary">Descargar PDF</a>
        </div>

        <div class="app-card overflow-x-auto overflow-y-hidden">
            <table class="min-w-[1460px] table-auto text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="min-w-[280px] px-4 py-3">Nombre del sistema</th>
                        <th class="min-w-[170px] px-4 py-3">Fecha de solicitud</th>
                        <th class="min-w-[190px] px-4 py-3">Fecha de creación</th>
                        <th class="min-w-[150px] px-4 py-3">Link de interés</th>
                        <th class="min-w-[320px] px-4 py-3">Estatus</th>
                        <th class="min-w-[150px] px-4 py-3">Trello</th>
                        <th class="min-w-[190px] px-4 py-3">Historial de cambios</th>
                        <th class="min-w-[210px] px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($systems as $system)
                        <tr class="border-t border-slate-200">
                            <td class="align-top px-4 py-4 font-medium leading-7 text-slate-900">{{ $system->name }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-600">{{ $system->request_date?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-600">{{ $system->created_at->format('d/m/Y H:i') }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">
                                @if ($system->links->isNotEmpty())
                                    <a href="{{ $system->links->first()->url }}" target="_blank" class="inline-flex text-sm text-[#960018] hover:underline">
                                        Abrir link
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin link</span>
                                @endif
                            </td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">
                                <div>{{ $system->status?->display_name ?? 'Sin estatus' }}</div>
                                @if ($system->status?->isTesting())
                                    <div class="mt-2 space-y-1 text-xs text-slate-500">
                                        <div>Tarjetas errores pendientes: {{ $system->pending_errors ?? 0 }}</div>
                                        <div>Tarjetas errores en proceso de solución: {{ $system->errors_in_progress ?? 0 }}</div>
                                        <div>Tarjetas en revisión: {{ $system->in_review ?? 0 }}</div>
                                        <div>Tarjetas finalizadas: {{ $system->finalized ?? 0 }}</div>
                                        <div>Total de tarjetas en trello: {{ $system->total_trello_cards }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">
                                @if ($system->trello_url)
                                    <a href="{{ $system->trello_url }}" target="_blank" class="inline-flex text-sm text-[#960018] hover:underline">
                                        Abrir Trello
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin Trello</span>
                                @endif
                            </td>
                            <td class="align-top px-4 py-4 text-slate-700">
                                <button class="app-button-secondary" type="button" x-data @click="$dispatch('open-modal', 'history-system-record-{{ $system->id }}')">
                                    Ver historial
                                </button>
                            </td>
                            <td class="align-top px-4 py-4">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    @can('systems.update')
                                        <button class="app-button-secondary" type="button" x-data @click="$dispatch('open-modal', 'edit-system-record-{{ $system->id }}')">
                                            Editar
                                        </button>
                                    @endcan
                                    @can('systems.delete')
                                        <form method="POST" action="{{ route('systems.destroy', $system) }}" onsubmit="return confirm('¿Deseas eliminar este sistema?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="app-button-secondary text-rose-600 hover:bg-rose-50" type="submit">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-500">No hay sistemas registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $systems->onEachSide(1)->links('vendor.pagination.compact') }}

        @foreach ($systems as $system)
            <x-modal name="history-system-record-{{ $system->id }}" :show="false" maxWidth="2xl">
                <div class="p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Historial de {{ $system->name }}</h3>
                            <p class="mt-1 text-sm text-slate-400">Consulta los cambios agrupados por el estatus que tenía el sistema en cada momento.</p>
                        </div>
                        <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'history-system-record-{{ $system->id }}')">Cerrar</button>
                    </div>

                    @php
                        $historyByStatus = $system->changeLogs->groupBy(fn ($log) => $log->status_group);
                    @endphp

                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('systems.history.report', ['system' => $system, 'format' => 'excel']) }}" class="app-button-secondary">Descargar historial Excel</a>
                        <a href="{{ route('systems.history.report', ['system' => $system, 'format' => 'pdf']) }}" class="app-button-secondary">Descargar historial PDF</a>
                    </div>

                    <div class="mt-6 max-h-[70vh] space-y-4 overflow-y-auto pr-2">
                        @forelse ($historyByStatus as $statusName => $logs)
                            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-900">Estatus: {{ $statusName }}</h4>
                                        <p class="text-xs text-slate-500">{{ $logs->count() }} cambio(s) registrado(s).</p>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @foreach ($logs as $log)
                                        <article class="rounded-2xl border border-slate-200 bg-white p-4">
                                            <div class="mb-2 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $log->localized_action }} · {{ optional($log->author)->name ?? 'Sistema' }} · {{ $log->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="prose max-w-none overflow-hidden break-words text-slate-700" style="overflow-wrap:anywhere;word-break:break-word;">{!! $log->rendered_content !!}</div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @empty
                            <p class="text-sm text-slate-500">No hay historial registrado para este sistema.</p>
                        @endforelse
                    </div>
                </div>
            </x-modal>

            @can('systems.update')
                <x-modal name="edit-system-record-{{ $system->id }}" :show="false" maxWidth="lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Editar sistema</h3>
                                <p class="mt-1 text-sm text-slate-400">Actualiza la información del sistema.</p>
                            </div>
                            <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'edit-system-record-{{ $system->id }}')">Cerrar</button>
                        </div>

                        <form method="POST" action="{{ route('systems.update', $system) }}" enctype="multipart/form-data" class="mt-6 space-y-4" x-data="systemMetricsForm({ testingStatusIds: @js($testingStatusIds), pendingErrors: @js((int) old('pending_errors', $system->pending_errors ?? 0)), errorsInProgress: @js((int) old('errors_in_progress', $system->errors_in_progress ?? 0)), inReview: @js((int) old('in_review', $system->in_review ?? 0)), finalizedCount: @js((int) old('finalized', $system->finalized ?? 0)) })">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="edit-system-request-date-{{ $system->id }}" class="app-label">Fecha de solicitud</label>
                                <input id="edit-system-request-date-{{ $system->id }}" name="request_date" type="date" class="app-input" value="{{ old('request_date', $system->request_date?->format('Y-m-d')) }}" required>
                            </div>

                            <div>
                                <label for="edit-system-name-{{ $system->id }}" class="app-label">Nombre del sistema</label>
                                <input id="edit-system-name-{{ $system->id }}" name="name" type="text" class="app-input" value="{{ old('name', $system->name) }}" required>
                            </div>

                            <div>
                                <label for="edit-system-link-{{ $system->id }}" class="app-label">Link</label>
                                <input id="edit-system-link-{{ $system->id }}" name="link" type="url" class="app-input" value="{{ old('link', $system->links->first()?->url) }}" placeholder="https://...">
                            </div>

                            <div>
                                <label for="edit-system-trello-{{ $system->id }}" class="app-label">Trello</label>
                                <input id="edit-system-trello-{{ $system->id }}" name="trello_url" type="url" class="app-input" value="{{ old('trello_url', $system->trello_url) }}" placeholder="https://trello.com/...">
                            </div>

                            <div>
                                <label for="edit-system-status-{{ $system->id }}" class="app-label">Estatus</label>
                                <select id="edit-system-status-{{ $system->id }}" name="system_status_id" class="app-input" x-ref="statusSelect" x-model="selectedStatusId" required>
                                    <option value="">Selecciona un estatus</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}" @selected(old('system_status_id', $system->system_status_id) == $status->id)>
                                                {{ $status->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="isTestingStatus()" x-transition class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
                                <div>
                                    <label for="edit-pending-errors-{{ $system->id }}" class="app-label">Tarjetas errores pendientes</label>
                                    <div class="app-stepper-control">
                                        <button type="button" class="app-stepper-button" @click="pendingErrors = Math.max(0, (Number(pendingErrors) || 0) - 1)">-</button>
                                        <input id="edit-pending-errors-{{ $system->id }}" name="pending_errors" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('pending_errors', $system->pending_errors ?? 0) }}" x-model.number="pendingErrors">
                                        <button type="button" class="app-stepper-button" @click="pendingErrors = (Number(pendingErrors) || 0) + 1">+</button>
                                    </div>
                                </div>
                                <div>
                                    <label for="edit-errors-in-progress-{{ $system->id }}" class="app-label">Tarjetas errores en proceso de solución</label>
                                    <div class="app-stepper-control">
                                        <button type="button" class="app-stepper-button" @click="errorsInProgress = Math.max(0, (Number(errorsInProgress) || 0) - 1)">-</button>
                                        <input id="edit-errors-in-progress-{{ $system->id }}" name="errors_in_progress" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('errors_in_progress', $system->errors_in_progress ?? 0) }}" x-model.number="errorsInProgress">
                                        <button type="button" class="app-stepper-button" @click="errorsInProgress = (Number(errorsInProgress) || 0) + 1">+</button>
                                    </div>
                                </div>
                                <div>
                                    <label for="edit-in-review-{{ $system->id }}" class="app-label">Tarjetas en revisión</label>
                                    <div class="app-stepper-control">
                                        <button type="button" class="app-stepper-button" @click="inReview = Math.max(0, (Number(inReview) || 0) - 1)">-</button>
                                        <input id="edit-in-review-{{ $system->id }}" name="in_review" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('in_review', $system->in_review ?? 0) }}" x-model.number="inReview">
                                        <button type="button" class="app-stepper-button" @click="inReview = (Number(inReview) || 0) + 1">+</button>
                                    </div>
                                </div>
                                <div>
                                    <label for="edit-finalized-{{ $system->id }}" class="app-label">Tarjetas finalizadas</label>
                                    <div class="app-stepper-control">
                                        <button type="button" class="app-stepper-button" @click="finalizedCount = Math.max(0, (Number(finalizedCount) || 0) - 1)">-</button>
                                        <input id="edit-finalized-{{ $system->id }}" name="finalized" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('finalized', $system->finalized ?? 0) }}" x-model.number="finalizedCount">
                                        <button type="button" class="app-stepper-button" @click="finalizedCount = (Number(finalizedCount) || 0) + 1">+</button>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="edit-total-trello-cards-{{ $system->id }}" class="app-label">Total de tarjetas en trello</label>
                                    <div id="edit-total-trello-cards-{{ $system->id }}" class="app-stepper-total" x-text="totalTrelloCards()"></div>
                                </div>
                            </div>

                            <div>
                                <label for="edit-system-new-attachments-{{ $system->id }}" class="app-label">Adjuntar archivos</label>
                                <input id="edit-system-new-attachments-{{ $system->id }}" name="attachments[]" type="file" class="app-file-input" multiple>
                                <p class="app-file-help">Puedes seleccionar uno o varios archivos para agregarlos al sistema.</p>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'edit-system-record-{{ $system->id }}')">Cancelar</button>
                                <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </x-modal>
            @endcan
        @endforeach
    </div>

    @can('systems.create')
        <x-modal name="create-system-record" :show="false" maxWidth="lg">
            <div class="p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Agregar sistema</h3>
                        <p class="mt-1 text-sm text-slate-400">Captura los datos del sistema y su movimiento.</p>
                    </div>
                    <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'create-system-record')">Cerrar</button>
                </div>

                <form method="POST" action="{{ route('systems.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4" x-data="systemMetricsForm({ testingStatusIds: @js($testingStatusIds), pendingErrors: @js((int) old('pending_errors', 0)), errorsInProgress: @js((int) old('errors_in_progress', 0)), inReview: @js((int) old('in_review', 0)), finalizedCount: @js((int) old('finalized', 0)) })">
                    @csrf
                    <div>
                        <label for="system-request-date" class="app-label">Fecha de solicitud</label>
                        <input id="system-request-date" name="request_date" type="date" class="app-input" value="{{ old('request_date') }}" required>
                    </div>

                    <div>
                        <label for="system-name" class="app-label">Nombre del sistema</label>
                        <input id="system-name" name="name" type="text" class="app-input" value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label for="system-link" class="app-label">Link</label>
                        <input id="system-link" name="link" type="url" class="app-input" value="{{ old('link') }}" placeholder="https://...">
                    </div>

                    <div>
                        <label for="system-trello" class="app-label">Trello</label>
                        <input id="system-trello" name="trello_url" type="url" class="app-input" value="{{ old('trello_url') }}" placeholder="https://trello.com/...">
                    </div>

                    <div>
                        <label for="system-status" class="app-label">Estatus</label>
                        <select id="system-status" name="system_status_id" class="app-input" x-ref="statusSelect" x-model="selectedStatusId" required>
                            <option value="">Selecciona un estatus</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" @selected(old('system_status_id') == $status->id)>
                                    {{ $status->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="isTestingStatus()" x-transition class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
                        <div>
                            <label for="pending-errors" class="app-label">Tarjetas errores pendientes</label>
                            <div class="app-stepper-control">
                                <button type="button" class="app-stepper-button" @click="pendingErrors = Math.max(0, (Number(pendingErrors) || 0) - 1)">-</button>
                                <input id="pending-errors" name="pending_errors" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('pending_errors', 0) }}" x-model.number="pendingErrors">
                                <button type="button" class="app-stepper-button" @click="pendingErrors = (Number(pendingErrors) || 0) + 1">+</button>
                            </div>
                        </div>

                        <div>
                            <label for="errors-in-progress" class="app-label">Tarjetas errores en proceso de solución</label>
                            <div class="app-stepper-control">
                                <button type="button" class="app-stepper-button" @click="errorsInProgress = Math.max(0, (Number(errorsInProgress) || 0) - 1)">-</button>
                                <input id="errors-in-progress" name="errors_in_progress" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('errors_in_progress', 0) }}" x-model.number="errorsInProgress">
                                <button type="button" class="app-stepper-button" @click="errorsInProgress = (Number(errorsInProgress) || 0) + 1">+</button>
                            </div>
                        </div>

                        <div>
                            <label for="in-review" class="app-label">Tarjetas en revisión</label>
                            <div class="app-stepper-control">
                                <button type="button" class="app-stepper-button" @click="inReview = Math.max(0, (Number(inReview) || 0) - 1)">-</button>
                                <input id="in-review" name="in_review" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('in_review', 0) }}" x-model.number="inReview">
                                <button type="button" class="app-stepper-button" @click="inReview = (Number(inReview) || 0) + 1">+</button>
                            </div>
                        </div>

                        <div>
                            <label for="finalized" class="app-label">Tarjetas finalizadas</label>
                            <div class="app-stepper-control">
                                <button type="button" class="app-stepper-button" @click="finalizedCount = Math.max(0, (Number(finalizedCount) || 0) - 1)">-</button>
                                <input id="finalized" name="finalized" type="number" min="0" step="1" class="app-stepper-input" value="{{ old('finalized', 0) }}" x-model.number="finalizedCount">
                                <button type="button" class="app-stepper-button" @click="finalizedCount = (Number(finalizedCount) || 0) + 1">+</button>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="total-trello-cards" class="app-label">Total de tarjetas en trello</label>
                            <div id="total-trello-cards" class="app-stepper-total" x-text="totalTrelloCards()"></div>
                        </div>
                    </div>

                    <div>
                        <label for="system-attachments" class="app-label">Adjuntar archivos</label>
                        <input id="system-attachments" name="attachments[]" type="file" class="app-file-input" multiple>
                        <p class="app-file-help">Puedes seleccionar uno o varios archivos para agregarlos al sistema.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'create-system-record')">Cancelar</button>
                        <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endcan
</x-app-layout>