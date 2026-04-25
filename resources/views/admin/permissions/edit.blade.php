<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Editar permiso</h2></x-slot>
    <div class="app-card p-6">
        <form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-validation-errors />
            <input name="name" class="app-input" value="{{ old('name', $permission->name) }}" required>
            <div class="flex items-center gap-3">
                <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar</button>
                <a href="{{ route('admin.permissions.index') }}" class="app-button-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
