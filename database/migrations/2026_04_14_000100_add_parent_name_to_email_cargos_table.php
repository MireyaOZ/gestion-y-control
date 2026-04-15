<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_cargos', function (Blueprint $table) {
            $table->string('parent_name')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('email_cargos', function (Blueprint $table) {
            $table->dropColumn('parent_name');
        });
    }
};