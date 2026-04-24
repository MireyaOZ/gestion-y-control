<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Correos</h2>
                <p class="text-sm text-white/80">Registro de solicitudes de movimientos de correo.</p>
            </div>
            @can('emails.create')
                <button class="app-button-light" type="button" x-data @click="$dispatch('open-modal', 'create-email-request')">
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
                        value="{{ $search }}"
                        class="app-input mt-0"
                        placeholder="Buscar por nombre, correo, cargo, dependencia o tipo de movimiento..."
                        @input="if ($event.target.value.trim() === '' && @js($search !== '')) { $el.form.requestSubmit(); }"
                    >
                </div>

                <div class="flex gap-3">
                    <button type="button" class="app-button-secondary" @click="toggle()" :aria-expanded="open.toString()">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.5 4.75A1.25 1.25 0 0 1 3.75 3.5h12.5a1.25 1.25 0 0 1 .97 2.04L12 11.95v3.55a1.25 1.25 0 0 1-.61 1.07l-2 1.2A1.25 1.25 0 0 1 7.5 16.7v-4.75L2.78 5.54a1.25 1.25 0 0 1-.28-.79Z" clip-rule="evenodd" />
                        </svg>
                        Filtros
                    </button>
                    <button type="submit" class="app-button" style="color: #ffffff !important;">Buscar</button>
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
                            <h3 class="text-lg font-semibold text-slate-900">Filtros de correos</h3>
                            <p class="mt-1 text-sm text-slate-500">Ajusta la búsqueda desde este panel lateral.</p>
                        </div>
                        <button type="button" class="rounded-2xl px-3 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" @click="close()">
                            Cerrar
                        </button>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-6">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="area_id" class="app-label">Buscar por área</label>
                        <select id="area_id" name="area_id" class="app-input">
                            <option value="">Selecciona un área</option>
                            @foreach ($areaOptions as $areaOption)
                                <option value="{{ $areaOption['id'] }}" @selected((int) $selectedAreaId === (int) $areaOption['id'])>
                                    {{ $areaOption['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">
                            Al elegir un área se mostrarán sus registros y todos los de sus dependencias hijas.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="movement_type_id" class="app-label">Tipo de movimiento</label>
                        <select id="movement_type_id" name="movement_type_id" class="app-input">
                            <option value="">Todos los movimientos</option>
                            @foreach ($movementTypes as $movementType)
                                <option value="{{ $movementType->id }}" @selected((int) $selectedMovementTypeId === (int) $movementType->id)>
                                    {{ $movementType->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">
                            Puedes combinar este filtro con área y texto para ubicar, por ejemplo, solo altas o bajas.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="status" class="app-label">Estatus</label>
                        <select id="status" name="status" class="app-input">
                            <option value="">Todos los estatus</option>
                            <option value="active" @selected($selectedStatus === 'active')>Activo</option>
                            <option value="inactive" @selected($selectedStatus === 'inactive')>Inactivo</option>
                        </select>
                        <p class="mt-2 text-xs text-slate-500">
                            Activo corresponde a Alta y Cambio de contraseña; Inactivo corresponde a Baja.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="request_date" class="app-label">Fecha de solicitud</label>
                        <input id="request_date" name="request_date" type="date" class="app-input" value="{{ $selectedRequestDate }}">
                        <p class="mt-2 text-xs text-slate-500">
                            Busca los registros usando la fecha capturada en el modal al crear o editar el correo.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="request_year" class="app-label">Año de solicitud</label>
                        <input id="request_year" name="request_year" type="number" min="2000" max="2100" class="app-input" value="{{ $selectedRequestYear }}" placeholder="2026">
                        <p class="mt-2 text-xs text-slate-500">
                            Filtra por el año de la fecha de solicitud capturada en el registro.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="created_at_from" class="app-label">Fecha desde</label>
                        <input id="created_at_from" name="created_at_from" type="date" class="app-input" value="{{ $selectedDateFrom }}">
                        <p class="mt-2 text-xs text-slate-500">
                            Si eliges solo esta fecha, se buscarán únicamente los registros de ese día.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="created_at_to" class="app-label">Fecha hasta</label>
                        <input id="created_at_to" name="created_at_to" type="date" class="app-input" value="{{ $selectedDateTo }}">
                        <p class="mt-2 text-xs text-slate-500">
                            Úsala junto con "Fecha desde" para buscar por un rango completo.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-between">
                        <a href="{{ route('emails.index') }}" class="app-button-secondary justify-center">Limpiar</a>
                        <button type="submit" class="app-button justify-center" style="color: #ffffff !important;" @click="showFilters = false">Aplicar filtros</button>
                    </div>
                </div>
            </div>

            @if ($selectedArea || $selectedMovementType || $selectedStatus || $selectedDateFrom || $selectedDateTo || $selectedRequestDate || $selectedRequestYear || $search !== '')
                <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
                    @if ($selectedArea)
                        <span>Área: <span class="font-semibold text-slate-900">{{ $selectedArea->name }}</span></span>
                    @endif
                    @if ($selectedMovementType)
                        <span>Movimiento: <span class="font-semibold text-slate-900">{{ $selectedMovementType->name }}</span></span>
                    @endif
                    @if ($selectedStatus)
                        <span>Estatus: <span class="font-semibold text-slate-900">{{ $statusLabel }}</span></span>
                    @endif
                    @if ($selectedRequestDate)
                        <span>Fecha de solicitud: <span class="font-semibold text-slate-900">{{ $requestDateLabel }}</span></span>
                    @endif
                    @if ($selectedRequestYear)
                        <span>Año de solicitud: <span class="font-semibold text-slate-900">{{ $requestYearLabel }}</span></span>
                    @endif
                    @if ($selectedDateFrom || $selectedDateTo)
                        <span>Fecha: <span class="font-semibold text-slate-900">{{ $dateLabel }}</span></span>
                    @endif
                    @if ($search !== '')
                        <span>Búsqueda: <span class="font-semibold text-slate-900">{{ $search }}</span></span>
                    @endif
                </div>
            @endif
        </form>

        <div class="space-y-3">
            <div class="flex justify-start gap-3">
                <a href="{{ route('emails.report', ['format' => 'excel', 'search' => $search, 'area_id' => $selectedAreaId, 'movement_type_id' => $selectedMovementTypeId, 'status' => $selectedStatus, 'request_date' => $selectedRequestDate, 'request_year' => $selectedRequestYear, 'created_at_from' => $selectedDateFrom, 'created_at_to' => $selectedDateTo]) }}" class="app-button-secondary">Descargar Excel</a>
                <a href="{{ route('emails.report', ['format' => 'pdf', 'search' => $search, 'area_id' => $selectedAreaId, 'movement_type_id' => $selectedMovementTypeId, 'status' => $selectedStatus, 'request_date' => $selectedRequestDate, 'request_year' => $selectedRequestYear, 'created_at_from' => $selectedDateFrom, 'created_at_to' => $selectedDateTo]) }}" class="app-button-secondary">Descargar PDF</a>
            </div>

            <div class="app-card overflow-x-auto overflow-y-hidden">
            <table class="min-w-[1640px] table-auto text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="min-w-[220px] px-4 py-3">Nombre</th>
                        <th class="min-w-[200px] px-4 py-3">Correo</th>
                        <th class="min-w-[240px] px-4 py-3">Cargo</th>
                        <th class="min-w-[320px] px-4 py-3">Superior jerárquico</th>
                        <th class="min-w-[170px] px-4 py-3">Tipo de movimiento</th>
                        <th class="min-w-[130px] px-4 py-3">Estatus</th>
                        <th class="min-w-[160px] px-4 py-3">Fecha de solicitud</th>
                        <th class="min-w-[170px] px-4 py-3">Fecha de creación</th>
                        <th class="min-w-[140px] px-4 py-3">Link de interés</th>
                        <th class="min-w-[180px] px-4 py-3">Historial de cambios</th>
                        <th class="min-w-[190px] px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($emailRequests as $emailRequest)
                        <tr class="border-t border-slate-200">
                            <td class="align-top px-4 py-4 font-medium leading-7 text-slate-900">{{ $emailRequest->name }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">{{ $emailRequest->email }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">{{ $emailRequest->cargo?->name ?? 'Sin cargo' }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">{{ $emailRequest->cargo?->parent_name ?? 'Sin area dependiente' }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">{{ $emailRequest->movementType->name }}</td>
                            <td class="align-top px-4 py-4 text-slate-700">
                                <x-status-pill :label="$emailRequest->operational_status" :tone="$emailRequest->operational_status_tone" />
                            </td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-600">{{ $emailRequest->request_date?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-600">{{ $emailRequest->created_at->format('d/m/Y H:i') }}</td>
                            <td class="align-top px-4 py-4 leading-7 text-slate-700">
                                @if ($emailRequest->links->isNotEmpty())
                                    <a href="{{ $emailRequest->links->first()->url }}" target="_blank" class="inline-flex text-sm text-[#960018] hover:underline">
                                        Abrir link
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin link</span>
                                @endif
                            </td>
                            <td class="align-top px-4 py-4 text-slate-700">
                                <button
                                    class="app-button-secondary"
                                    type="button"
                                    x-data
                                    @click="$dispatch('open-modal', 'email-history-{{ $emailRequest->id }}')"
                                >
                                    Ver historial
                                </button>
                            </td>
                            <td class="align-top px-4 py-4">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    @can('emails.update')
                                        <button
                                            class="app-button-secondary"
                                            type="button"
                                            x-data
                                            @click="$dispatch('open-modal', 'edit-email-request-{{ $emailRequest->id }}')"
                                        >
                                            Editar
                                        </button>
                                    @endcan
                                    @can('emails.delete')
                                        <form method="POST" action="{{ route('emails.destroy', $emailRequest) }}" onsubmit="return confirm('¿Deseas eliminar esta solicitud de correo?');">
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
                            <td colspan="11" class="px-4 py-6 text-center text-slate-500">No hay solicitudes de correos registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{ $emailRequests->onEachSide(1)->links('vendor.pagination.compact') }}

        @foreach ($emailRequests as $emailRequest)
            @can('emails.update')
                <x-modal name="edit-email-request-{{ $emailRequest->id }}" :show="false" maxWidth="lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Editar solicitud de correo</h3>
                                <p class="mt-1 text-sm text-slate-400">Actualiza los datos del registro seleccionado.</p>
                            </div>
                            <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'edit-email-request-{{ $emailRequest->id }}')">Cerrar</button>
                        </div>

                        <form method="POST" action="{{ route('emails.update', $emailRequest) }}" class="mt-6 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="edit-request-date-{{ $emailRequest->id }}" class="app-label">Fecha de solicitud</label>
                                <input id="edit-request-date-{{ $emailRequest->id }}" name="request_date" type="date" class="app-input" value="{{ old('request_date', $emailRequest->request_date?->format('Y-m-d')) }}" required>
                            </div>

                            <div>
                                <label for="edit-name-{{ $emailRequest->id }}" class="app-label">Nombre</label>
                                <input id="edit-name-{{ $emailRequest->id }}" name="name" type="text" class="app-input" value="{{ old('name', $emailRequest->name) }}" required>
                            </div>

                            <div>
                                <label for="edit-email-{{ $emailRequest->id }}" class="app-label">Correo</label>
                                <input id="edit-email-{{ $emailRequest->id }}" name="email" type="email" class="app-input" value="{{ old('email', $emailRequest->email) }}" required>
                            </div>

                            <div>
                                <label for="edit-link-{{ $emailRequest->id }}" class="app-label">Link</label>
                                <input id="edit-link-{{ $emailRequest->id }}" name="link" type="url" class="app-input" value="{{ old('link', $emailRequest->links->first()?->url) }}" placeholder="https://...">
                            </div>

                            <div>
                                <label for="edit-email-cargo-{{ $emailRequest->id }}" class="app-label">Cargo</label>
                                <select id="edit-email-cargo-{{ $emailRequest->id }}" name="email_cargo_id" class="app-input" required>
                                    <option value="">Selecciona un cargo</option>
                                    @foreach ($cargos as $cargo)
                                        <option value="{{ $cargo->id }}" @selected(old('email_cargo_id', $emailRequest->email_cargo_id) == $cargo->id)>
                                            {{ $cargo->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="edit-email-movement-type-{{ $emailRequest->id }}" class="app-label">Tipo de movimiento</label>
                                <select id="edit-email-movement-type-{{ $emailRequest->id }}" name="email_movement_type_id" class="app-input" required>
                                    <option value="">Selecciona una opción</option>
                                    @foreach ($movementTypes as $movementType)
                                        <option value="{{ $movementType->id }}" @selected(old('email_movement_type_id', $emailRequest->email_movement_type_id) == $movementType->id)>
                                            {{ $movementType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'edit-email-request-{{ $emailRequest->id }}')">Cancelar</button>
                                <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </x-modal>
            @endcan

            <x-modal name="email-history-{{ $emailRequest->id }}" :show="false" maxWidth="2xl">
                <div class="p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Historial de cambios</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ $emailRequest->name }} · {{ $emailRequest->email }}</p>
                        </div>
                        <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'email-history-{{ $emailRequest->id }}')">Cerrar</button>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('emails.history.report', ['emailRequest' => $emailRequest, 'format' => 'excel']) }}" class="app-button-secondary">Descargar historial Excel</a>
                        <a href="{{ route('emails.history.report', ['emailRequest' => $emailRequest, 'format' => 'pdf']) }}" class="app-button-secondary">Descargar historial PDF</a>
                    </div>

                    <div class="mt-6 max-h-[70vh] space-y-3 overflow-y-auto pr-2">
                        @forelse ($emailRequest->changeLogs as $log)
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="mb-2 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $log->localized_action }} · {{ optional($log->author)->name ?? 'Sistema' }} · {{ $log->created_at->format('d/m/Y H:i') }}</div>
                                <div class="prose max-w-none text-slate-700">{!! $log->content !!}</div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No hay cambios registrados.</p>
                        @endforelse
                    </div>
                </div>
            </x-modal>
        @endforeach
    </div>

    @can('emails.create')
        <x-modal name="create-email-request" :show="false" maxWidth="lg">
            <div class="p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Agregar solicitud de correo</h3>
                        <p class="mt-1 text-sm text-slate-400">Captura los datos para registrar el movimiento solicitado.</p>
                    </div>
                    <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'create-email-request')">Cerrar</button>
                </div>

                <form method="POST" action="{{ route('emails.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="request_date" class="app-label">Fecha de solicitud</label>
                        <input id="request_date" name="request_date" type="date" class="app-input" value="{{ old('request_date') }}" required>
                    </div>

                    <div>
                        <label for="name" class="app-label">Nombre</label>
                        <input id="name" name="name" type="text" class="app-input" value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label for="email" class="app-label">Correo</label>
                        <input id="email" name="email" type="email" class="app-input" value="{{ old('email') }}" required>
                    </div>

                    <div>
                        <label for="link" class="app-label">Link</label>
                        <input id="link" name="link" type="url" class="app-input" value="{{ old('link') }}" placeholder="https://...">
                    </div>

                    <div>
                        <label for="email_cargo_id" class="app-label">Cargo</label>
                        <select id="email_cargo_id" name="email_cargo_id" class="app-input" required>
                            <option value="">Selecciona un cargo</option>
                            @foreach ($cargos as $cargo)
                                <option value="{{ $cargo->id }}" @selected(old('email_cargo_id') == $cargo->id)>
                                    {{ $cargo->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="email_movement_type_id" class="app-label">Tipo de movimiento</label>
                        <select id="email_movement_type_id" name="email_movement_type_id" class="app-input" required>
                            <option value="">Selecciona una opción</option>
                            @foreach ($movementTypes as $movementType)
                                <option value="{{ $movementType->id }}" @selected(old('email_movement_type_id') == $movementType->id)>
                                    {{ $movementType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'create-email-request')">Cancelar</button>
                        <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endcan
</x-app-layout>