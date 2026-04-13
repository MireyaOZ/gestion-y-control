@csrf

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label class="app-label" for="title">Título</label>
        <input id="title" name="title" type="text" class="app-input" value="{{ old('title', $project->title ?? '') }}" required>
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <label class="app-label" for="priority_id">Prioridad</label>
        <select id="priority_id" name="priority_id" class="app-input" required>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->id }}" @selected(old('priority_id', $project->priority_id ?? '') == $priority->id)>{{ ucfirst($priority->name) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="app-label" for="project_status_id">Estado del proyecto</label>
        <select id="project_status_id" name="project_status_id" class="app-input" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}" @selected(old('project_status_id', $project->project_status_id ?? '') == $status->id)>{{ ucfirst($status->name) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="app-label" for="start_date">Fecha de inicio</label>
        <input id="start_date" name="start_date" type="date" class="app-input" value="{{ old('start_date', optional($project->start_date ?? null)?->format('Y-m-d')) }}">
    </div>

    <div>
        <label class="app-label" for="end_date">Fecha de fin</label>
        <input id="end_date" name="end_date" type="date" class="app-input" value="{{ old('end_date', optional($project->end_date ?? null)?->format('Y-m-d')) }}">
    </div>

    <div class="lg:col-span-2">
        <label class="app-label" for="description">Descripción</label>
        <textarea id="description" name="description" rows="6" class="app-input">{{ old('description', $project->description ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="app-button" type="submit">Guardar</button>
    <a href="{{ route('projects.index') }}" class="app-button-secondary">Cancelar</a>
</div>
