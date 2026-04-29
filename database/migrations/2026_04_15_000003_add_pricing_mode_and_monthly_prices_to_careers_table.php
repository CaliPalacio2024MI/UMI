<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->string('pricing_mode', 20)->default('uniform')->after('semesters');
            $table->json('monthly_prices')->nullable()->after('pricing_mode');
        });
    }

    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn(['pricing_mode', 'monthly_prices']);
        });
    }
};
