<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-white">Roles</h2>
            <a href="{{ route('admin.roles.create') }}" class="app-button-light">Agregar nuevo rol</a>
        </div>
    </x-slot>

    <div class="app-card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-white/5 text-left text-slate-400">
                <tr><th class="px-4 py-3">Rol</th><th class="px-4 py-3">Permisos</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr class="border-t border-white/10">
                        <td class="px-4 py-3 text-slate-100">
                            <div>{{ \App\Support\PermissionCatalog::roleLabel($role->name) }}</div>
                            <div class="text-xs text-slate-400">{{ $role->name }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $role->permissions->pluck('name')->map(fn ($permissionName) => \App\Support\PermissionCatalog::permissionLabel($permissionName))->join(', ') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.roles.edit', $role) }}" class="text-emerald-300">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
