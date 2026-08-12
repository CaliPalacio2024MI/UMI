<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */public function up(): void
{
    Schema::table('course_sessions', function (Blueprint $table) {
        $table->string('instructor_name')->nullable()->after('end_time');
    });
}

public function down(): void
{
    Schema::table('course_sessions', function (Blueprint $table) {
        if (Schema::hasColumn('course_sessions', 'instructor_name')) {
            $table->dropColumn('instructor_name');
        }
    });
}
};
