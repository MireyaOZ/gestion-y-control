<?php

namespace App\Http\Controllers;

use App\Models\EmailRequest;
use App\Models\Subtask;
use App\Models\SystemRecord;
use App\Models\Task;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();

        $tasksCount = Task::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('assignees', fn ($assignees) => $assignees->whereKey($user->id));
            }))
            ->count();

        $subtasksCount = Subtask::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('assignees', fn ($assignees) => $assignees->whereKey($user->id))
                    ->orWhereHas('task', fn ($tasks) => $tasks->where('created_by', $user->id));
            }))
            ->count();

        $emailsCount = EmailRequest::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where('created_by', $user->id))
            ->count();

        $systemsCount = SystemRecord::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where('created_by', $user->id))
            ->count();

        $upcomingTasks = Task::query()
            ->with(['status', 'priority'])
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('assignees', fn ($assignees) => $assignees->whereKey($user->id));
            }))
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        return view('dashboard', compact('tasksCount', 'subtasksCount', 'emailsCount', 'systemsCount', 'upcomingTasks'));
    }
}
