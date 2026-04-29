<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('nombre_aula', 255)->nullable()->after('numero_aula');
            $table->foreignId('career_id')->nullable()->after('nombre_aula')->constrained('careers')->nullOnDelete();
            $table->string('tipo_materia', 100)->nullable()->after('career_id');
        });

        foreach (DB::table('facilities')->select('id', 'numero_aula', 'nombre_aula')->get() as $row) {
            if ($row->nombre_aula === null || $row->nombre_aula === '') {
                DB::table('facilities')->where('id', $row->id)->update(['nombre_aula' => $row->numero_aula]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('career_id');
            $table->dropColumn(['nombre_aula', 'tipo_materia']);
        });
    }
};
