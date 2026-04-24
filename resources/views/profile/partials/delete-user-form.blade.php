<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-slate-900">
            Eliminar cuenta
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Si eliminas tu cuenta, todos sus datos asociados se eliminarán de forma permanente.
        </p>
    </header>

    <button
        type="button"
        class="app-button-secondary text-rose-600 hover:bg-rose-50"
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar cuenta</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-slate-900">
                ¿Deseas eliminar tu cuenta?
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Esta acción eliminará permanentemente tu cuenta. Ingresa tu contraseña para confirmarlo.
            </p>

            <div class="mt-6" x-data="passwordField()">
                <label for="password" class="app-label">Contraseña</label>

                <div class="relative max-w-md">
                    <input
                        id="password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="app-input pr-12"
                        placeholder="Ingresa tu contraseña"
                    />

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

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="app-button-secondary" x-on:click="$dispatch('close')">
                    Cancelar
                </button>

                <button type="submit" class="app-button-secondary text-rose-600 hover:bg-rose-50">
                    Eliminar cuenta
                </button>
            </div>
        </form>
    </x-modal>
</section>
