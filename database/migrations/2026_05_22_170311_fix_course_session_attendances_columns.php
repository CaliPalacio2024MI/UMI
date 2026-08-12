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
    Schema::table('course_session_attendances', function (Blueprint $table) {
        if (!Schema::hasColumn('course_session_attendances', 'course_session_id')) {
            $table->foreignId('course_session_id')
                ->after('id')
                ->constrained('course_sessions')
                ->onDelete('cascade');
        }

        if (!Schema::hasColumn('course_session_attendances', 'rfc')) {
            $table->string('rfc')->after('course_session_id');
        }

        if (!Schema::hasColumn('course_session_attendances', 'attended_at')) {
            $table->dateTime('attended_at')->nullable()->after('rfc');
        }
    });
}

public function down(): void
{
    Schema::table('course_session_attendances', function (Blueprint $table) {
        if (Schema::hasColumn('course_session_attendances', 'course_session_id')) {
            $table->dropColumn('course_session_id');
        }

        if (Schema::hasColumn('course_session_attendances', 'rfc')) {
            $table->dropColumn('rfc');
        }

        if (Schema::hasColumn('course_session_attendances', 'attended_at')) {
            $table->dropColumn('attended_at');
        }
    });
}
};
