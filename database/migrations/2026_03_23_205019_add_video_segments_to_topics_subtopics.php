<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar video_segments a TOPICS
        Schema::table('topics', function (Blueprint $table) {
            $table->json('video_segments')->nullable()->after('turtle_voice');
        });

        // Agregar video_segments a SUBTOPICS
        Schema::table('subtopics', function (Blueprint $table) {
            $table->json('video_segments')->nullable()->after('turtle_voice');
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('video_segments');
        });

        Schema::table('subtopics', function (Blueprint $table) {
            $table->dropColumn('video_segments');
        });
    }
};
