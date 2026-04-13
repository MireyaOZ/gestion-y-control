<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-white">Permisos</h2>
            <a href="{{ route('admin.permissions.create') }}" class="app-button">Nuevo permiso</a>
        </div>
    </x-slot>

    <div class="app-card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-white/5 text-left text-slate-400">
                <tr><th class="px-4 py-3">Permiso</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($permissions as $permission)
                    <tr class="border-t border-white/10">
                        <td class="px-4 py-3 text-slate-100">{{ $permission->name }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.permissions.edit', $permission) }}" class="text-emerald-300">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $permissions->links() }}
</x-app-layout>
