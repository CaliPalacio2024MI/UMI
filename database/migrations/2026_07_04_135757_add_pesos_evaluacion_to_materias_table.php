<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->unsignedTinyInteger('peso_tareas')->default(20)->after('num_parciales');
            $table->unsignedTinyInteger('peso_evaluaciones')->default(60)->after('peso_tareas');
            $table->unsignedTinyInteger('peso_asistencias')->default(20)->after('peso_evaluaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn(['peso_tareas', 'peso_evaluaciones', 'peso_asistencias']);
        });
    }
};
