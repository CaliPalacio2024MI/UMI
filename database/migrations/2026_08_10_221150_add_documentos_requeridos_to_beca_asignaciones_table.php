<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beca_asignaciones', function (Blueprint $table) {
            $table->json('documentos_requeridos')->nullable()->after('beca_id');
        });
    }

    public function down(): void
    {
        Schema::table('beca_asignaciones', function (Blueprint $table) {
            $table->dropColumn('documentos_requeridos');
        });
    }
};
