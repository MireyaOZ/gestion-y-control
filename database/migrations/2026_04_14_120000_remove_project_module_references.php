<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('project_id');
        });

        DB::table('role_has_permissions')
            ->whereIn('permission_id', function ($query) {
                $query->select('id')
                    ->from('permissions')
                    ->where('name', 'like', 'projects.%');
            })
            ->delete();

        DB::table('permissions')
            ->where('name', 'like', 'projects.%')
            ->delete();
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete()->after('priority_id');
        });
    }
};