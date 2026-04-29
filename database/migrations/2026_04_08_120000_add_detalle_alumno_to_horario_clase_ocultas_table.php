<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detalle por alumno al guardar la cajita (carrera, semestre, matrícula, materia, horario).
     * fila con alumno_id NULL = comportamiento anterior (ocultar todo el horario_clase).
     */
    public function up(): void
    {
        Schema::table('horario_clase_ocultas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['horario_clase_id']);
            $table->dropUnique(['user_id', 'horario_clase_id']);
        });

        Schema::table('horario_clase_ocultas', function (Blueprint $table) {
            $table->foreignId('alumno_id')->nullable()->after('horario_clase_id')->constrained('users')->nullOnDelete();
            $table->string('carrera_nombre', 255)->nullable()->after('alumno_id');
            $table->string('semestre', 32)->nullable()->after('carrera_nombre');
            $table->string('matricula', 64)->nullable()->after('semestre');
            $table->string('materia_nombre', 255)->nullable()->after('matricula');
            $table->text('horario_resumen')->nullable()->after('materia_nombre');
            $table->string('alumno_nombre', 255)->nullable()->after('horario_resumen');

            $table->index(['user_id', 'horario_clase_id', 'alumno_id'], 'hco_user_hc_alumno_idx');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('horario_clase_id')->references('id')->on('horario_clases')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horario_clase_ocultas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['horario_clase_id']);
            $table->dropForeign(['alumno_id']);
            $table->dropIndex('hco_user_hc_alumno_idx');
            $table->dropColumn([
                'alumno_id',
                'carrera_nombre',
                'semestre',
                'matricula',
                'materia_nombre',
                'horario_resumen',
                'alumno_nombre',
            ]);
            $table->unique(['user_id', 'horario_clase_id']);
        });

        Schema::table('horario_clase_ocultas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('horario_clase_id')->references('id')->on('horario_clases')->cascadeOnDelete();
        });
    }
};
