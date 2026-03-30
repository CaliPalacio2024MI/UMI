<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('topics', function (Blueprint $table) {
            // Identificar si el topic es una actividad
            $table->boolean('is_activity')->default(false)->after('show_turtle');

            // Tipo de actividad (Cuestionario, SopaDeLetras, etc.)
            $table->string('activity_type')->nullable()->after('is_activity');

            // Contenido de la actividad (JSON)
            $table->json('activity_content')->nullable()->after('activity_type');
        });
    }

    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn(['is_activity', 'activity_type', 'activity_content']);
        });
    }
};
