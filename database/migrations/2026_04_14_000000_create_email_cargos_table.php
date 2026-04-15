<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_cargos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('email_requests', function (Blueprint $table) {
            $table->foreignId('email_cargo_id')->nullable()->after('email')->constrained('email_cargos')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('email_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('email_cargo_id');
        });

        Schema::dropIfExists('email_cargos');
    }
};