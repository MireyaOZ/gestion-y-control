<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->date('request_date')->nullable();
            $table->string('name');
            $table->text('trello_url')->nullable();
            $table->foreignId('system_status_id')->nullable()->constrained('system_statuses')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('pending_errors')->nullable();
            $table->unsignedInteger('errors_in_progress')->nullable();
            $table->unsignedInteger('in_review')->nullable();
            $table->unsignedInteger('finalized')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('systems');
        Schema::dropIfExists('system_statuses');
    }
};