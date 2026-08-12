<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formulario_campos', function (Blueprint $table) {
            $table->id();
            $table->string('etiqueta');
            $table->string('nombre_campo', 100)->unique();
            $table->string('tipo', 30)->default('text');
            $table->text('opciones')->nullable();
            $table->string('placeholder', 200)->nullable();
            $table->boolean('obligatorio')->default(true);
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formulario_campos');
    }
};
