<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->text('objetivo')->nullable()->after('descripcion');
            $table->text('temario')->nullable()->after('objetivo');
            $table->text('infografia')->nullable()->after('temario');
        });
    }

    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn(['objetivo', 'temario', 'infografia']);
        });
    }
};
