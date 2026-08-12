<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulario_campos', function (Blueprint $table) {
            $table->string('seccion', 20)->nullable()->after('orden');      // tutor | postulante
            $table->string('despues_de', 100)->nullable()->after('seccion'); // campo base anchor
        });
    }

    public function down(): void
    {
        Schema::table('formulario_campos', function (Blueprint $table) {
            $table->dropColumn(['seccion', 'despues_de']);
        });
    }
};
