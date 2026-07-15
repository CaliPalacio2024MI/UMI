<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_clase_id')->constrained('horario_clases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // alumno
            $table->unsignedBigInteger('period_id')->nullable();
            $table->unsignedTinyInteger('parcial'); // 1..N
            $table->unsignedTinyInteger('calificacion')->nullable(); // 0..100
            $table->boolean('confirmada')->default(false); // bloquea edición del docente al confirmar
            $table->timestamps();

            $table->unique(['horario_clase_id', 'user_id', 'parcial']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
