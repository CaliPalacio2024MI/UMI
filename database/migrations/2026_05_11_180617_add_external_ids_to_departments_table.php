<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('department', function (Blueprint $table) {

            // ID del departamento en la API
            $table->string('external_id')
                ->nullable()
                ->after('id');

            // ID de la propiedad/unidad en la API
            $table->string('external_property_id')
                ->nullable()
                ->after('external_id');

        });
    }

    public function down(): void
    {
        Schema::table('department', function (Blueprint $table) {

            $table->dropColumn([
                'external_id',
                'external_property_id'
            ]);

        });
    }
};
