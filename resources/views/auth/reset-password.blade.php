<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Restablecer contraseña</h1>
            <p class="mt-2 text-sm text-slate-500">Define una nueva contraseña para recuperar el acceso a tu cuenta.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5" x-data="passwordField()">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="app-label">Correo electrónico</label>
                <input id="email" class="app-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="app-label">Nueva contraseña</label>
                <div class="relative">
                    <input id="password" class="app-input pr-12" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" />
                    <button type="button" class="absolute inset-y-0 right-0 inline-flex items-center justify-center px-4 text-slate-400 transition hover:text-[#960018] focus:outline-none" @click="toggle()" :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
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
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="app-label">Confirmar contraseña</label>
                <input id="password_confirmation" class="app-input" :type="showPassword ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button class="app-button w-full" type="submit" style="color: #ffffff !important;">
                Guardar nueva contraseña
            </button>
        </form>
    </div>
</x-guest-layout>
