<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Usuarios</h2>
                <p class="text-sm text-white/80">Administración de usuarios, roles y activación.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="app-button-light">Agregar nuevo usuario</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex gap-3">
            <a href="{{ route('admin.roles.index') }}" class="app-button-secondary">Roles</a>
            <a href="{{ route('admin.permissions.index') }}" class="app-button-secondary">Permisos</a>
        </div>

        <div class="app-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5 text-left text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Roles</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t border-white/10">
                            <td class="px-4 py-3 text-slate-100">{{ $user->name }}<div class="text-xs text-slate-400">{{ $user->email }}</div></td>
                            <td class="px-4 py-3 text-slate-300">{{ $user->roles->pluck('name')->map(fn ($roleName) => \App\Support\PermissionCatalog::roleLabel($roleName))->join(', ') }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @if (auth()->user()?->can('admin.access') && ! session()->has('impersonator_id') && auth()->id() !== $user->id && $user->is_active)
                                        <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                                            @csrf
                                            <button type="submit" class="text-sky-300">Acceder</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-300">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $users->onEachSide(1)->links('vendor.pagination.compact') }}
    </div>
</x-app-layout>
