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
        Schema::create('tarea_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('fecha_entrega')->nullable();
            $table->unsignedInteger('puntaje_obtenido')->nullable();
            $table->timestamps();

            $table->unique(['tarea_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarea_entregas');
    }
};
