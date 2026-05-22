<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_periods', function (Blueprint $table) {
            $table->boolean('attendance_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('course_periods', function (Blueprint $table) {
            $table->dropColumn('attendance_enabled');
        });
    }
};