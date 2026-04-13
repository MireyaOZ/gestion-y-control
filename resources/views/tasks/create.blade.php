<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-white">Nueva tarea</h2>
    </x-slot>

    <div class="app-card p-6">
        <form method="POST" action="{{ route('tasks.store') }}">
            @include('tasks._form')
        </form>
    </div>
</x-app-layout>
