<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_session_attendances')) {
            Schema::create('course_session_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_session_id')->constrained('course_sessions')->onDelete('cascade');
                $table->string('rfc');
                $table->dateTime('attended_at')->nullable();
                $table->timestamps();

                $table->unique(['course_session_id', 'rfc']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_session_attendances');
    }
};
