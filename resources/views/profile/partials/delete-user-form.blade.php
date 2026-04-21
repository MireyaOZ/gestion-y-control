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
        x-data=""
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

            <div class="mt-6">
                <label for="password" class="app-label">Contraseña</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="app-input max-w-md"
                    placeholder="Ingresa tu contraseña"
                />

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
