<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_classifications', function (Blueprint $table) {
            $table->boolean('visible_landing')->default(true)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('career_classifications', function (Blueprint $table) {
            $table->dropColumn('visible_landing');
        });
    }
};