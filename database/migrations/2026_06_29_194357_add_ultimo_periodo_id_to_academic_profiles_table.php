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
        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->foreignId('ultimo_periodo_id')->nullable()->after('status')->constrained('periods')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->dropForeign(['ultimo_periodo_id']);
            $table->dropColumn('ultimo_periodo_id');
        });
    }
};
