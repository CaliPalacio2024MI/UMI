<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Permite el mismo nombre de materia en distintas carreras; único por (career_id, nombre).
     */
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
            $table->unique(['career_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropUnique(['career_id', 'nombre']);
            $table->unique('nombre');
        });
    }
};
