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
        Schema::table('billings', function (Blueprint $table) {
            $table->boolean('cargo_moratorio_aplicado')->default(false)->after('cargo_monetario');
            $table->date('fecha_cargo_moratorio')->nullable()->after('cargo_moratorio_aplicado');
            $table->date('fecha_prorroga_fin')->nullable()->after('fecha_cargo_moratorio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn(['cargo_moratorio_aplicado', 'fecha_cargo_moratorio', 'fecha_prorroga_fin']);
        });
    }
};
