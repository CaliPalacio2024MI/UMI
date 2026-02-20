<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
{
    DB::table('lead_seguimientos')
        ->where('estado', 'Alumno')
        ->update(['estado' => 'Aspirante']);
}

public function down(): void
{
    DB::table('lead_seguimientos')
        ->where('estado', 'Aspirante')
        ->update(['estado' => 'Alumno']);
}
};