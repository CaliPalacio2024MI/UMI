<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas del grupo de un horario presencial que se perdieron en la migración
 * del módulo de Cursos (existían en la rama lms_maria_y_erick):
 *  - group_departments:  departamentos elegidos para el grupo
 *  - group_workstations: puestos elegidos para el grupo
 *  - group_participants: personas (anfitriones) que forman la lista
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('group_departments')) {
            Schema::create('group_departments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained()->cascadeOnDelete();
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['group_id', 'department_id']);
            });
        }

        if (! Schema::hasTable('group_workstations')) {
            Schema::create('group_workstations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained()->cascadeOnDelete();
                $table->foreignId('workstation_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['group_id', 'workstation_id']);
            });
        }

        if (! Schema::hasTable('group_participants')) {
            Schema::create('group_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['group_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_participants');
        Schema::dropIfExists('group_workstations');
        Schema::dropIfExists('group_departments');
    }
};
