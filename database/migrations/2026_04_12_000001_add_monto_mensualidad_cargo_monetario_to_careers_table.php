<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->decimal('monto_mensualidad', 12, 2)->nullable()->after('credits');
            $table->decimal('cargo_monetario', 14, 2)->nullable()->after('monto_mensualidad');
        });
    }

    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn(['monto_mensualidad', 'cargo_monetario']);
        });
    }
};
