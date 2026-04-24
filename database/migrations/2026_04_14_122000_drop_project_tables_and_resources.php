<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attachments')
            ->where('attachable_type', 'project')
            ->pluck('path')
            ->filter()
            ->each(fn (string $path) => Storage::disk('public')->delete($path));

        DB::table('attachments')
            ->where('attachable_type', 'project')
            ->delete();

        DB::table('resource_links')
            ->where('linkable_type', 'project')
            ->delete();

        DB::table('comments')
            ->where('commentable_type', 'project')
            ->delete();

        DB::table('change_logs')
            ->where('loggable_type', 'project')
            ->delete();

        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_statuses');
    }

    public function down(): void
    {
        Schema::create('project_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('project_status_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('priority_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }
};
