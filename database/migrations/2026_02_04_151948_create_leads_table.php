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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            // Datos del Tutor
            $table->string('tutor_nombre');
            $table->string('tutor_paterno');
            $table->string('tutor_materno');
            $table->string('telefono1');
            $table->string('telefono2')->nullable();

            // Datos del Alumno
            $table->string('alumno_nombre');
            $table->string('alumno_paterno');
            $table->string('alumno_materno');
            $table->string('rfc')->nullable();
            $table->string('curp')->nullable();
            
            // Metadatos
            $table->string('origen')->default('formulario_publico');
            $table->string('clasificacion')->default('nuevo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
