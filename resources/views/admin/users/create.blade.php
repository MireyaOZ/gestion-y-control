<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Nuevo usuario</h2></x-slot>
    <div class="app-card p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <x-validation-errors />
            <input name="name" class="app-input" placeholder="Nombre" value="{{ old('name') }}" required>
            <input name="email" type="email" class="app-input" placeholder="Correo" value="{{ old('email') }}" required>
            <input name="password" type="password" class="app-input" placeholder="Contraseña" required>
            <p class="text-xs text-slate-400">La contraseña debe tener al menos 8 caracteres.</p>
            <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Usuario activo</label>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($roles as $role)
                    <label class="rounded-2xl border border-white/10 px-3 py-2 text-sm text-slate-200"><input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(collect(old('roles', []))->contains($role->name))> {{ $role->name }}</label>
                @endforeach
            </div>
            <button class="app-button" type="submit">Guardar</button>
        </form>
    </div>
</x-app-layout>
