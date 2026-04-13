<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\ChangeLog;
use App\Models\Comment;
use App\Models\Project;
use App\Models\ResourceLink;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use App\Policies\ProjectPolicy;
use App\Policies\SubtaskPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'project' => Project::class,
            'task' => Task::class,
            'subtask' => Subtask::class,
            'attachment' => Attachment::class,
            'resource_link' => ResourceLink::class,
            'comment' => Comment::class,
            'change_log' => ChangeLog::class,
        ]);

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Subtask::class, SubtaskPolicy::class);
    }
}
