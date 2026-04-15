<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'gestor_proyectos')
            ->update(['name' => 'gestor_operativo']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'gestor_operativo')
            ->update(['name' => 'gestor_proyectos']);
    }
};