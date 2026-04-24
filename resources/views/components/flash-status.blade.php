@php
    $flashStatus = session('status');
    $flashMessage = match ($flashStatus) {
        'profile-updated' => 'La información del perfil se actualizó correctamente.',
        'password-updated' => 'La contraseña se actualizó correctamente.',
        'verification-link-sent' => 'Se envió un nuevo enlace de verificación a tu correo electrónico.',
        default => $flashStatus,
    };
@endphp

@if ($flashStatus)
    <div
        x-data="timedVisibility(4000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
    >
        {{ $flashMessage }}
    </div>
@endif
