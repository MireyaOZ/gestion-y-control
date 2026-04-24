<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Recuperar contraseña</h1>
            <p class="mt-2 text-sm text-slate-500">Escribe tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>
        </div>

        <x-auth-session-status class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="app-label">Correo electrónico</label>
                <input id="email" class="app-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <button class="app-button w-full" type="submit" style="color: #ffffff !important;">
                Enviar enlace de recuperación
            </button>
        </form>
    </div>
</x-guest-layout>
