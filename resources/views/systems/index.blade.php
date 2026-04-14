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

        <div class="app-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Nombre del sistema</th>
                        <th class="px-4 py-3">Fecha de creación</th>
                        <th class="px-4 py-3">Link de interés</th>
                        <th class="px-4 py-3">Estatus</th>
                        <th class="px-4 py-3">Adjuntos</th>
                        <th class="px-4 py-3">Trello</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($systems as $system)
                        <tr class="border-t border-slate-200">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $system->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $system->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                @if ($system->links->isNotEmpty())
                                    <a href="{{ $system->links->first()->url }}" target="_blank" class="inline-flex text-sm text-[#960018] hover:underline">
                                        Abrir link
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin link</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $system->status?->name ?? 'Sin estatus' }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                <button class="inline-flex text-sm text-[#960018] hover:underline" type="button" x-data @click="$dispatch('open-modal', 'attachments-system-record-{{ $system->id }}')">
                                    {{ $system->attachments->count() }} adjunto(s)
                                </button>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                @if ($system->trello_url)
                                    <a href="{{ $system->trello_url }}" target="_blank" class="inline-flex text-sm text-[#960018] hover:underline">
                                        Abrir Trello
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin Trello</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button class="app-button-secondary" type="button" x-data @click="$dispatch('open-modal', 'attachments-system-record-{{ $system->id }}')">
                                        Adjuntos
                                    </button>
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
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500">No hay sistemas registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $systems->links() }}

        @foreach ($systems as $system)
            <x-modal name="attachments-system-record-{{ $system->id }}" :show="false" maxWidth="lg">
                <div class="p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Adjuntos de {{ $system->name }}</h3>
                            <p class="mt-1 text-sm text-slate-400">Consulta o carga archivos relacionados a este sistema.</p>
                        </div>
                        <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'attachments-system-record-{{ $system->id }}')">Cerrar</button>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($system->attachments as $attachment)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 p-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $attachment->original_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ number_format($attachment->size / 1024, 1) }} KB</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a class="text-xs text-[#960018] hover:underline" href="{{ asset('storage/'.$attachment->path) }}" target="_blank">Abrir</a>
                                    @can('systems.update')
                                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs text-rose-600" type="submit">Eliminar</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No hay archivos adjuntos.</p>
                        @endforelse
                    </div>

                    @can('systems.update')
                        <form method="POST" action="{{ route('attachments.store', ['system', $system->id]) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <label for="system-attachments-{{ $system->id }}" class="app-label">Adjuntar archivos</label>
                                <input id="system-attachments-{{ $system->id }}" type="file" name="file" class="block w-full text-sm text-slate-600">
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'attachments-system-record-{{ $system->id }}')">Cancelar</button>
                                <button class="app-button" type="submit">Adjuntar</button>
                            </div>
                        </form>
                    @endcan
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

                        <form method="POST" action="{{ route('systems.update', $system) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                            @csrf
                            @method('PATCH')
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
                                <select id="edit-system-status-{{ $system->id }}" name="system_status_id" class="app-input" required>
                                    <option value="">Selecciona un estatus</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}" @selected(old('system_status_id', $system->system_status_id) == $status->id)>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="edit-system-new-attachments-{{ $system->id }}" class="app-label">Adjuntar archivos</label>
                                <input id="edit-system-new-attachments-{{ $system->id }}" name="attachments[]" type="file" class="block w-full text-sm text-slate-600" multiple>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'edit-system-record-{{ $system->id }}')">Cancelar</button>
                                <button class="app-button" type="submit">Guardar cambios</button>
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

                <form method="POST" action="{{ route('systems.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
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
                        <select id="system-status" name="system_status_id" class="app-input" required>
                            <option value="">Selecciona un estatus</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" @selected(old('system_status_id') == $status->id)>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="system-attachments" class="app-label">Adjuntar archivos</label>
                        <input id="system-attachments" name="attachments[]" type="file" class="block w-full text-sm text-slate-600" multiple>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'create-system-record')">Cancelar</button>
                        <button class="app-button" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endcan
</x-app-layout>