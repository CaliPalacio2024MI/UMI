<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Clases que el usuario de control académico marcó como "guardadas" en la cajita.
     * Se ocultan de la tabla izquierda en Clases. Antes solo se guardaba en sesión.
     */
    public function up(): void
    {
        Schema::create('horario_clase_ocultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('horario_clase_id')->constrained('horario_clases')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'horario_clase_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_clase_ocultas');
    }
};
