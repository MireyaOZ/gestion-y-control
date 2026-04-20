@csrf

<input type="hidden" name="task_id" value="{{ old('task_id', $subtask->task_id ?? $selectedTaskId ?? '') }}">

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label class="app-label" for="title">Título</label>
        <input id="title" name="title" type="text" class="app-input" value="{{ old('title', $subtask->title ?? '') }}" required>
    </div>

    <div>
        <label class="app-label" for="due_date">Fecha de vencimiento</label>
        <input id="due_date" name="due_date" type="date" class="app-input" value="{{ old('due_date', optional($subtask->due_date ?? null)?->format('Y-m-d')) }}">
    </div>

    <div>
        <label class="app-label" for="task_status_id">Estado</label>
        <select id="task_status_id" name="task_status_id" class="app-input" required>
            <option value="" @selected(old('task_status_id', $subtask->task_status_id ?? '') === '')>Seleccione</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}" @selected(old('task_status_id', $subtask->task_status_id ?? '') == $status->id)>{{ ucfirst($status->name) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="app-label" for="priority_id">Prioridad</label>
        <select id="priority_id" name="priority_id" class="app-input" required>
            <option value="" @selected(old('priority_id', $subtask->priority_id ?? '') === '')>Seleccione</option>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->id }}" @selected(old('priority_id', $subtask->priority_id ?? '') == $priority->id)>{{ ucfirst($priority->name) }}</option>
            @endforeach
        </select>
    </div>

    <div class="lg:col-span-2">
        <label class="app-label" for="description">Descripción</label>
        <textarea id="description" name="description" rows="6" class="app-input">{{ old('description', $subtask->description ?? '') }}</textarea>
    </div>

    <div class="lg:col-span-2">
        <label class="app-label">Usuarios asignados</label>
        <x-search-multi-select
            name="assignee_ids"
            :endpoint="route('search.users')"
            :selected="old('assignee_ids')
                ? collect(old('assignee_ids'))->map(fn ($id) => ['id' => (int) $id, 'label' => 'Usuario #'.$id, 'meta' => ''])
                : (($subtask->assignees ?? collect())->map(fn ($user) => ['id' => $user->id, 'label' => $user->name, 'meta' => $user->email])->values()->all())"
        />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="app-button" type="submit">Guardar</button>
    <a href="{{ isset($subtask) ? route('subtasks.show', $subtask) : route('tasks.show', old('task_id', $selectedTaskId ?? '')) }}" class="app-button-secondary">Cancelar</a>
</div>
