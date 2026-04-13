<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-white">Nueva subtarea</h2>
    </x-slot>

    <div class="app-card p-6">
        <form method="POST" action="{{ route('subtasks.store') }}">
            @include('subtasks._form')
        </form>
    </div>
</x-app-layout>
