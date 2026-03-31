<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los leads usan el mismo catálogo que Control Admin (tabla careers).
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
        });

        $validCareerIds = DB::table('careers')->pluck('id')->all();
        if ($validCareerIds !== []) {
            DB::table('leads')
                ->whereNotNull('carrera_id')
                ->whereNotIn('carrera_id', $validCareerIds)
                ->update(['carrera_id' => null]);
        } else {
            DB::table('leads')->whereNotNull('carrera_id')->update(['carrera_id' => null]);
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('carrera_id')
                ->references('id')
                ->on('careers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('carrera_id')
                ->references('id')
                ->on('carreras')
                ->nullOnDelete();
        });
    }
};
