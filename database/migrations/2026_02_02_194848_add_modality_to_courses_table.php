<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    if (!Schema::hasColumn('courses', 'modality')) {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('modality', ['presencial', 'virtual', 'hibrida'])
                  ->default('hibrida')
                  ->after('description');
        });
    }
}
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('modality');
        });
    }
};
