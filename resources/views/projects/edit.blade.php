<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-white">Editar proyecto</h2>
    </x-slot>

    <div class="app-card p-6">
        <form method="POST" action="{{ route('projects.update', $project) }}">
            @method('PUT')
            @include('projects._form')
        </form>
    </div>
</x-app-layout>
