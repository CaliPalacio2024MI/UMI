<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina columnas ya no usadas: número, sección, capacidad, ubicación, tipo de aula física.
     * Se conservan: id, nombre_aula, career_id, tipo_materia, timestamps.
     */
    public function up(): void
    {
        foreach (DB::table('facilities')->select('id', 'numero_aula', 'nombre_aula')->get() as $row) {
            $nombre = trim((string) ($row->nombre_aula ?? ''));
            if ($nombre === '') {
                $fallback = trim((string) ($row->numero_aula ?? ''));
                DB::table('facilities')->where('id', $row->id)->update([
                    'nombre_aula' => $fallback !== '' ? $fallback : ('Aula '.$row->id),
                ]);
            }
        }

        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn([
                'numero_aula',
                'seccion',
                'capacidad',
                'ubicacion',
                'tipo',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('numero_aula', 10)->nullable()->after('id');
            $table->string('seccion')->nullable()->after('tipo_materia');
            $table->integer('capacidad')->nullable()->after('seccion');
            $table->string('ubicacion', 100)->nullable()->after('capacidad');
            $table->string('tipo', 20)->default('Aula')->after('ubicacion');
        });

        foreach (DB::table('facilities')->select('id', 'nombre_aula')->get() as $row) {
            $n = trim((string) ($row->nombre_aula ?? ''));
            $short = mb_strlen($n) > 10 ? mb_substr($n, 0, 10) : $n;
            DB::table('facilities')->where('id', $row->id)->update([
                'numero_aula' => $short !== '' ? $short : '—',
                'seccion' => 'Sin sección',
            ]);
        }
    }
};
