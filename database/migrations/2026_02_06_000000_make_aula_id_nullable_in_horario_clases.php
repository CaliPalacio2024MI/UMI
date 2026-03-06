<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hace opcional el aula en horarios (por el momento se puede guardar vacío).
     */
    public function up(): void
    {
        Schema::table('horario_clases', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
        });
        Schema::table('horario_clases', function (Blueprint $table) {
            $table->unsignedBigInteger('aula_id')->nullable()->change();
            $table->foreign('aula_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('horario_clases', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
        });
        Schema::table('horario_clases', function (Blueprint $table) {
            $table->unsignedBigInteger('aula_id')->nullable(false)->change();
            $table->foreign('aula_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }
};
