<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Nuevo usuario</h2></x-slot>
    <div class="app-card p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <x-validation-errors />
            <input name="name" class="app-input" placeholder="Nombre" value="{{ old('name') }}" required>
            <input name="email" type="email" class="app-input" placeholder="Correo" value="{{ old('email') }}" required>
            <div x-data="passwordField()">
                <div class="relative">
                    <input
                        name="password"
                        class="app-input pr-12"
                        placeholder="Contraseña"
                        :type="showPassword ? 'text' : 'password'"
                        required
                    >
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-4 text-slate-400 transition hover:text-[#960018] focus:outline-none"
                        @click="toggle()"
                        :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    >
                        <svg x-show="!showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg x-show="showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2 2 0 0 0 12 14a2 2 0 0 0 1.414-.586" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.363 5.365A9.466 9.466 0 0 1 12 5c4.478 0 8.268 2.943 9.542 7a9.771 9.771 0 0 1-4.22 5.337M6.228 6.228A9.776 9.776 0 0 0 2.458 12c1.274 4.057 5.064 7 9.542 7 1.61 0 3.13-.38 4.478-1.055" />
                        </svg>
                    </button>
                </div>
            </div>
            <p class="text-xs text-slate-400">La contraseña debe tener al menos 8 caracteres.</p>
            <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Usuario activo</label>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($roles as $role)
                    <label class="rounded-2xl border border-white/10 px-3 py-2 text-sm text-slate-200">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(collect(old('roles', []))->contains($role->name))>
                        {{ \App\Support\PermissionCatalog::roleLabel($role->name) }}
                        <span class="block text-xs text-slate-400">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
            <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar</button>
        </form>
    </div>
</x-app-layout>
