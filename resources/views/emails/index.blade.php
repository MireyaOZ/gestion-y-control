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

        <form class="app-card p-4">
            <input
                name="search"
                value="{{ $search }}"
                class="app-input"
                placeholder="Buscar por nombre, correo o tipo de movimiento..."
            >
        </form>

        <div class="app-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Tipo de movimiento</th>
                        <th class="px-4 py-3">Fecha de creación</th>
                        <th class="px-4 py-3">Link de interés</th>
                        <th class="px-4 py-3">Historial de cambios</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($emailRequests as $emailRequest)
                        <tr class="border-t border-slate-200">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $emailRequest->name }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $emailRequest->email }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $emailRequest->movementType->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $emailRequest->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                @if ($emailRequest->links->isNotEmpty())
                                    <a href="{{ $emailRequest->links->first()->url }}" target="_blank" class="inline-flex text-sm text-[#960018] hover:underline">
                                        Abrir link
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin link</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <button
                                    class="app-button-secondary"
                                    type="button"
                                    x-data
                                    @click="$dispatch('open-modal', 'email-history-{{ $emailRequest->id }}')"
                                >
                                    Ver historial
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
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
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500">No hay solicitudes de correos registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $emailRequests->links() }}

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
                                <button class="app-button" type="submit">Guardar cambios</button>
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

                    <div class="mt-6 max-h-[70vh] space-y-3 overflow-y-auto pr-2">
                        @forelse ($emailRequest->changeLogs as $log)
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="mb-2 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $log->action }} · {{ optional($log->author)->name ?? 'Sistema' }} · {{ $log->created_at->format('d/m/Y H:i') }}</div>
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
                        <button class="app-button" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endcan
</x-app-layout>