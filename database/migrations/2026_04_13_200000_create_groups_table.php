<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        if (Schema::hasTable('groups')) {
            return;
        }

        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->nullable()
                ->constrained('courses')
                ->nullOnDelete();

            $table->foreignId('institution_id')
                ->nullable()
                ->constrained('institutions')
                ->nullOnDelete();

            $table->string('type')->nullable();
            $table->unsignedInteger('min_participants')->nullable();
            $table->unsignedInteger('max_participants')->nullable();
            $table->string('name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
