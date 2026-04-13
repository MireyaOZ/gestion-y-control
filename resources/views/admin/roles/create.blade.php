<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Nuevo rol</h2></x-slot>
    <div class="app-card p-6">
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-4">
            @csrf
            <x-validation-errors />
            <input name="name" class="app-input" placeholder="Nombre del rol" value="{{ old('name') }}" required>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($permissions as $permission)
                    <label class="rounded-2xl border border-white/10 px-3 py-2 text-sm text-slate-200"><input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(collect(old('permissions', []))->contains($permission->name))> {{ $permission->name }}</label>
                @endforeach
            </div>
            <button class="app-button" type="submit">Guardar</button>
        </form>
    </div>
</x-app-layout>
