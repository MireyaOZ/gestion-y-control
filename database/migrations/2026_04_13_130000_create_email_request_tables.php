<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_movement_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('email_cargos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('email_requests', function (Blueprint $table) {
            $table->id();
            $table->date('request_date')->nullable();
            $table->string('name');
            $table->string('email');
            $table->foreignId('email_cargo_id')->nullable()->constrained('email_cargos')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('email_movement_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_requests');
        Schema::dropIfExists('email_cargos');
        Schema::dropIfExists('email_movement_types');
    }
};