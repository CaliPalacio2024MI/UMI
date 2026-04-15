<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_session', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_session_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('group_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_session');
    }
};