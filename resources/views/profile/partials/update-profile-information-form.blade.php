<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">
            Información del perfil
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Actualiza los datos de tu cuenta y el correo asociado.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="app-label">Nombre</label>
            <input id="name" name="name" type="text" class="app-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="app-label">Correo electrónico</label>
            <input id="email" name="email" type="email" class="app-input" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-slate-700">
                        Tu correo electrónico aún no ha sido verificado.

                        <button form="send-verification" class="rounded-md text-sm font-medium text-[#960018] underline transition hover:text-[#7c0014] focus:outline-none focus:ring-2 focus:ring-[#960018] focus:ring-offset-2">
                            Haz clic aquí para reenviar el correo de verificación.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">
                            Se envió un nuevo enlace de verificación a tu correo electrónico.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="app-button">Guardar cambios</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="timedVisibility()"
                    x-show="show"
                    x-transition
                    class="text-sm text-slate-500"
                >Guardado.</p>
            @endif
        </div>
    </form>
</section>
