<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Editar permiso</h2></x-slot>
    <div class="app-card p-6">
        <form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-validation-errors />
            <input name="name" class="app-input" value="{{ old('name', $permission->name) }}" required>
            <button class="app-button" type="submit">Guardar</button>
        </form>
    </div>
</x-app-layout>
