<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horario_clases', function (Blueprint $table) {
            $table->unsignedBigInteger('period_id')->nullable()->after('id');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('horario_clases', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
};