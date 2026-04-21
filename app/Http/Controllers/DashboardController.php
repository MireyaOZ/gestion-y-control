<?php

namespace App\Http\Controllers;

use App\Models\EmailRequest;
use App\Models\SystemRecord;
use App\Models\Task;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();

        $tasksQuery = $this->visibleTasksQuery($user);
        $emailsQuery = $this->visibleEmailsQuery($user);
        $systemsQuery = $this->visibleSystemsQuery($user);

        $tasksCount = (clone $tasksQuery)->count();
        $emailsCount = (clone $emailsQuery)->count();
        $systemsCount = (clone $systemsQuery)->count();

        $taskItems = (clone $tasksQuery)->with('status')->get();
        $emailItems = (clone $emailsQuery)->with('movementType')->get();
        $systemItems = (clone $systemsQuery)->with('status')->get();

        $taskChart = $this->buildChartData(
            $taskItems->map(fn (Task $task) => [
                'label' => ucfirst($task->status?->name ?? 'Sin estado'),
                'tone' => $task->status?->slug,
            ])->all(),
            'Sin tareas registradas.'
        );

        $emailChart = $this->buildChartData(
            $emailItems->map(fn (EmailRequest $email) => [
                'label' => $email->operational_status,
                'tone' => $email->operational_status_tone,
            ])->all(),
            'Sin correos registrados.'
        );

        $systemChart = $this->buildChartData(
            $systemItems->map(fn (SystemRecord $system) => [
                'label' => $system->status?->display_name ?? 'Sin estado',
                'tone' => $system->status?->slug,
            ])->all(),
            'Sin sistemas registrados.'
        );

        $taskMonthlyChart = $this->buildMonthlyChartData(
            $taskItems,
            fn (Task $task) => $task->created_at,
            'Sin tareas registradas en los ultimos meses.',
            '#960018'
        );

        $emailMonthlyChart = $this->buildMonthlyChartData(
            $emailItems,
            fn (EmailRequest $email) => $email->request_date ?? $email->created_at,
            'Sin correos registrados en los ultimos meses.',
            '#0f766e'
        );

        $systemMonthlyChart = $this->buildMonthlyChartData(
            $systemItems,
            fn (SystemRecord $system) => $system->request_date ?? $system->created_at,
            'Sin sistemas registrados en los ultimos meses.',
            '#1d4ed8'
        );

        return view('dashboard', compact(
            'tasksCount',
            'emailsCount',
            'systemsCount',
            'taskChart',
            'emailChart',
            'systemChart',
            'taskMonthlyChart',
            'emailMonthlyChart',
            'systemMonthlyChart'
        ));
    }

    protected function visibleTasksQuery($user): Builder
    {
        return Task::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('assignees', fn ($assignees) => $assignees->whereKey($user->id));
            }));
    }

    protected function visibleEmailsQuery($user): Builder
    {
        return EmailRequest::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where('created_by', $user->id));
    }

    protected function visibleSystemsQuery($user): Builder
    {
        return SystemRecord::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where('created_by', $user->id));
    }

    protected function buildChartData(array $items, string $emptyMessage): array
    {
        if ($items === []) {
            return [
                'total' => 0,
                'items' => [],
                'emptyMessage' => $emptyMessage,
            ];
        }

        $grouped = collect($items)
            ->groupBy(fn (array $item) => $item['label'])
            ->map(function ($group, $label) {
                $first = $group->first();

                return [
                    'label' => (string) $label,
                    'count' => $group->count(),
                    'tone' => $first['tone'] ?? null,
                    'color' => $this->resolveChartColor($first['tone'] ?? null),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $total = (int) $grouped->sum('count');
        $max = max(1, (int) $grouped->max('count'));

        return [
            'total' => $total,
            'emptyMessage' => $emptyMessage,
            'items' => $grouped->map(fn (array $item) => $item + [
                'percentage' => (int) round(($item['count'] / $total) * 100),
                'width' => (int) round(($item['count'] / $max) * 100),
            ])->all(),
        ];
    }

    protected function buildMonthlyChartData(Collection $items, callable $dateResolver, string $emptyMessage, string $color): array
    {
        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset))
            ->push(now()->startOfMonth())
            ->values();

        $countsByMonth = $items
            ->map(function ($item) use ($dateResolver) {
                $date = $dateResolver($item);

                if (! $date instanceof CarbonInterface) {
                    return null;
                }

                return $date->copy()->startOfMonth()->format('Y-m');
            })
            ->filter()
            ->countBy();

        $total = (int) $countsByMonth->sum();
        $max = max(1, (int) $countsByMonth->max());

        $chartItems = $months->map(function ($month) use ($countsByMonth, $max, $color) {
            $key = $month->format('Y-m');
            $count = (int) ($countsByMonth[$key] ?? 0);

            return [
                'label' => ucfirst($month->translatedFormat('M Y')),
                'shortLabel' => ucfirst($month->translatedFormat('M')),
                'count' => $count,
                'width' => (int) round(($count / $max) * 100),
                'color' => $color,
            ];
        })->all();

        return [
            'total' => $total,
            'items' => $chartItems,
            'emptyMessage' => $emptyMessage,
        ];
    }

    protected function resolveChartColor(?string $tone): string
    {
        return match ($tone) {
            'pendiente' => '#ec4899',
            'en-progreso' => '#eab308',
            'completada' => '#16a34a',
            'cancelada' => '#64748b',
            'rechazado' => '#dc2626',
            'urgente' => '#b91c1c',
            'alta' => '#f97316',
            'media' => '#eab308',
            'baja' => '#22c55e',
            'finalizado' => '#16a34a',
            'en-proceso-de-diagramacion' => '#fb7185',
            'en-proceso-de-reunion' => '#f59e0b',
            'en-pruebas' => '#8b5cf6',
            'proceso-de-validacion' => '#0ea5e9',
            'visto-bueno-del-diagrama' => '#14b8a6',
            default => '#960018',
        };
    }
}
