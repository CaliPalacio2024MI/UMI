<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->onDelete('cascade');
            // Proceso al que pertenece el documento solicitado:
            // inscripcion, expediente_alumnos, becas, titulacion, servicio_social, practicas_profesionales
            $table->string('proceso', 50)->index();
            $table->string('nombre');                       // Ej. "CURP"
            $table->text('descripcion')->nullable();
            $table->string('tipos_archivo')->nullable();    // Extensiones permitidas (CSV): "pdf,jpg,png"
            $table->unsignedInteger('cantidad')->default(1);// Cuántos archivos se esperan
            $table->boolean('obligatorio')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'proceso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};
