<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Editar usuario</h2></x-slot>
    <div class="app-card p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-validation-errors />
            <input name="name" class="app-input" value="{{ old('name', $user->name) }}" required>
            <input name="email" type="email" class="app-input" value="{{ old('email', $user->email) }}" required>
            <input name="password" type="password" class="app-input" placeholder="Nueva contraseña (opcional)">
            <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))> Usuario activo</label>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($roles as $role)
                    <label class="rounded-2xl border border-white/10 px-3 py-2 text-sm text-slate-200">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(collect(old('roles', $user->roles->pluck('name')->all()))->contains($role->name))>
                        {{ \App\Support\PermissionCatalog::roleLabel($role->name) }}
                        <span class="block text-xs text-slate-400">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
            <div class="flex items-center gap-3">
                <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar</button>
                <a href="{{ route('admin.users.index') }}" class="app-button-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
