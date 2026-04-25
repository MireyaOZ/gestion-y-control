<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Nuevo permiso</h2></x-slot>
    <div class="app-card p-6">
        <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-4">
            @csrf
            <x-validation-errors />
            <input name="name" class="app-input" placeholder="ej. tasks.archive" value="{{ old('name') }}" required>
            <div class="flex items-center gap-3">
                <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar</button>
                <a href="{{ route('admin.permissions.index') }}" class="app-button-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
