<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_links', function (Blueprint $table) {
            $table->text('url')->change();
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->text('trello_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('resource_links', function (Blueprint $table) {
            $table->string('url')->change();
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->string('trello_url')->nullable()->change();
        });
    }
};
