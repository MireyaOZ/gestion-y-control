<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table): void {
            $table->unsignedInteger('pending_errors')->nullable()->after('system_status_id');
            $table->unsignedInteger('errors_in_progress')->nullable()->after('pending_errors');
            $table->unsignedInteger('in_review')->nullable()->after('errors_in_progress');
            $table->unsignedInteger('finalized')->nullable()->after('in_review');
        });
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table): void {
            $table->dropColumn([
                'pending_errors',
                'errors_in_progress',
                'in_review',
                'finalized',
            ]);
        });
    }
};