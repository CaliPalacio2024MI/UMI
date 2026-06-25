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
    Schema::table('materias', function (Blueprint $table) {
        $table->unsignedBigInteger('career_classification_id')->nullable()->after('career_id');
        $table->foreign('career_classification_id')->references('id')->on('career_classifications')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('materias', function (Blueprint $table) {
        $table->dropForeign(['career_classification_id']);
        $table->dropColumn('career_classification_id');
    });
}
};
