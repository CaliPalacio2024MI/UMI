<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'estudiante')
            ->update(['display_name' => 'Alumno']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'estudiante')
            ->update(['display_name' => 'Estudiante']);
    }
};
