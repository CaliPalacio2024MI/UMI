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
        Schema::create('clase_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_clase_id')->constrained('horario_clases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('fecha');
            $table->boolean('presente')->default(false);
            $table->timestamps();

            $table->unique(['horario_clase_id', 'user_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clase_asistencias');
    }
};
