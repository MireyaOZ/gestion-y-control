<?php

namespace Database\Seeders;

use App\Models\Priority;
use App\Models\ProjectStatus;
use App\Models\TaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'en proceso de reunion',
            'en proceso de diagramacion',
            'proceso de validacion',
            'visto bueno de diagramacion',
        ] as $status) {
            ProjectStatus::query()->updateOrCreate(
                ['slug' => Str::slug($status)],
                ['name' => $status],
            );
        }

        foreach ([
            'pendiente',
            'en progreso',
            'completada',
            'cancelada',
            'rechazado',
        ] as $status) {
            TaskStatus::query()->updateOrCreate(
                ['slug' => Str::slug($status)],
                ['name' => $status],
            );
        }

        foreach ([
            ['name' => 'baja', 'weight' => 1],
            ['name' => 'media', 'weight' => 2],
            ['name' => 'alta', 'weight' => 3],
            ['name' => 'urgente', 'weight' => 4],
        ] as $priority) {
            Priority::query()->updateOrCreate(
                ['slug' => Str::slug($priority['name'])],
                $priority,
            );
        }
    }
}
