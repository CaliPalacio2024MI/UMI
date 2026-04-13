<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {

            $table->string('name')->after('id');

            $table->enum('type', ['abierto', 'cerrado'])
                  ->default('abierto')
                  ->after('name');

            $table->integer('min_participants')->after('type');
            $table->integer('max_participants')->after('min_participants');

            $table->foreignId('institution_id')
                  ->after('max_participants')
                  ->constrained()
                  ->cascadeOnDelete();
        });

        // TABLA PIVOTE: group_department
        Schema::create('group_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
        });

        // TABLA PIVOTE: group_workstation
        Schema::create('group_workstation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workstation_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_workstation');
        Schema::dropIfExists('group_department');

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'type',
                'min_participants',
                'max_participants',
                'institution_id'
            ]);
        });
    }
};
