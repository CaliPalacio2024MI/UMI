<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda los filtros con los que se armó la lista de participantes del grupo
 * (propiedad: departamentos/puestos; universidad: clasificaciones/carreras/materias),
 * para que al reabrir la ventana aparezca tal como se dejó.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('groups', 'filters')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->json('filters')->nullable()->after('max_participants');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('groups', 'filters')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropColumn('filters');
            });
        }
    }
};
