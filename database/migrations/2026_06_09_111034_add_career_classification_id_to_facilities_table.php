<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->unsignedBigInteger('career_classification_id')->nullable()->after('nombre_aula');
            $table->foreign('career_classification_id')->references('id')->on('career_classifications')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['career_classification_id']);
            $table->dropColumn('career_classification_id');
        });
    }
};