@csrf

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label class="app-label" for="title">Título</label>
        <input id="title" name="title" type="text" class="app-input uppercase" value="{{ old('title', $task->title ?? '') }}" required>
    </div>

    <div>
        <label class="app-label" for="due_date">Fecha de vencimiento</label>
        <input id="due_date" name="due_date" type="date" class="app-input" value="{{ old('due_date', optional($task->due_date ?? null)?->format('Y-m-d')) }}">
    </div>

    <div>
        <label class="app-label" for="task_status_id">Estado</label>
        <select id="task_status_id" name="task_status_id" class="app-input" required>
            <option value="" @selected(old('task_status_id', $task->task_status_id ?? '') === '')>Seleccione</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}" @selected(old('task_status_id', $task->task_status_id ?? '') == $status->id)>{{ ucfirst($status->name) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="app-label" for="priority_id">Prioridad</label>
        <select id="priority_id" name="priority_id" class="app-input priority-select" data-priority-select required>
            <option value="" @selected(old('priority_id', $task->priority_id ?? '') === '')>Seleccione</option>
            @foreach ($priorities as $priority)
                @php
                    $priorityStyle = match (\Illuminate\Support\Str::lower($priority->name)) {
                        'baja' => 'background-color:#dcfce7;color:#15803d;',
                        'media' => 'background-color:#fef3c7;color:#a16207;',
                        'alta' => 'background-color:#fee2e2;color:#b91c1c;',
                        'urgente' => 'background-color:#b91c1c;color:#ffffff;',
                        default => 'background-color:#ffffff;color:#0f172a;',
                    };

                    $priorityTone = match (\Illuminate\Support\Str::lower($priority->name)) {
                        'baja' => ['background' => '#dcfce7', 'border' => '#86efac', 'text' => '#15803d'],
                        'media' => ['background' => '#fef3c7', 'border' => '#fcd34d', 'text' => '#a16207'],
                        'alta' => ['background' => '#fee2e2', 'border' => '#fca5a5', 'text' => '#b91c1c'],
                        'urgente' => ['background' => '#b91c1c', 'border' => '#7f1d1d', 'text' => '#ffffff'],
                        default => ['background' => '#ffffff', 'border' => '#cbd5e1', 'text' => '#0f172a'],
                    };
                @endphp
                <option
                    value="{{ $priority->id }}"
                    data-priority-background="{{ $priorityTone['background'] }}"
                    data-priority-border="{{ $priorityTone['border'] }}"
                    data-priority-text="{{ $priorityTone['text'] }}"
                    style="{{ $priorityStyle }}"
                    @selected(old('priority_id', $task->priority_id ?? '') == $priority->id)
                >{{ ucfirst($priority->name) }}</option>
            @endforeach
        </select>
    </div>

    <div class="lg:col-span-2">
        <label class="app-label" for="description">Descripción</label>
        <textarea id="description" name="description" rows="6" class="app-input uppercase">{{ old('description', $task->description ?? '') }}</textarea>
    </div>

    <div class="lg:col-span-2">
        <label class="app-label">Usuarios asignados</label>
        <x-search-multi-select
            name="assignee_ids"
            :endpoint="route('search.users')"
            :selected="old('assignee_ids')
                ? collect(old('assignee_ids'))->map(fn ($id) => ['id' => (int) $id, 'label' => 'Usuario #'.$id, 'meta' => ''])
                : (($task->assignees ?? collect())->map(fn ($user) => ['id' => $user->id, 'label' => $user->name, 'meta' => $user->email])->values()->all())"
        />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="app-button" style="color: #ffffff !important;" type="submit">Guardar</button>
    <a href="{{ route('tasks.index') }}" class="app-button-secondary">Cancelar</a>
</div>
