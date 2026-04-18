<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_requests', function (Blueprint $table): void {
            $table->date('request_date')->nullable()->after('id');
        });

        Schema::table('systems', function (Blueprint $table): void {
            $table->date('request_date')->nullable()->after('id');
        });

        DB::table('email_requests')->update([
            'request_date' => DB::raw('DATE(created_at)'),
        ]);

        DB::table('systems')->update([
            'request_date' => DB::raw('DATE(created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('email_requests', function (Blueprint $table): void {
            $table->dropColumn('request_date');
        });

        Schema::table('systems', function (Blueprint $table): void {
            $table->dropColumn('request_date');
        });
    }
};