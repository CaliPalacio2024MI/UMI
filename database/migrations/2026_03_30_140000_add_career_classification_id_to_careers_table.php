<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->foreignId('career_classification_id')
                ->nullable()
                ->after('institution_id')
                ->constrained('career_classifications')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('career_classification_id');
        });
    }
};
