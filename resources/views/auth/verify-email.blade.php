<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Verificar correo</h1>
            <p class="mt-2 text-sm text-slate-500">Antes de continuar, confirma tu correo usando el enlace que enviamos. Si no lo recibiste, puedes solicitar uno nuevo.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                Se envió un nuevo enlace de verificación al correo registrado.
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}" class="sm:flex-1">
                @csrf
                <button class="app-button w-full sm:w-auto" type="submit" style="color: #ffffff !important;">
                    Reenviar verificación
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-button-secondary w-full sm:w-auto">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
