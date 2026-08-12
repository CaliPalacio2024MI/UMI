<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requirements', function (Blueprint $table) {
            $table->string('slug', 80)->nullable()->after('activo')->index();
        });

        $institutionId = DB::table('institutions')->where('is_universidad', true)->value('id') ?? 4;

        $basicos = [
            ['slug' => 'acta_nacimiento',   'nombre' => 'Acta de Nacimiento',          'tipos_archivo' => 'pdf',         'obligatorio' => true,  'orden' => 1],
            ['slug' => 'certificado_prepa', 'nombre' => 'Certificado de Preparatoria', 'tipos_archivo' => 'pdf',         'obligatorio' => true,  'orden' => 2],
            ['slug' => 'curp',              'nombre' => 'CURP',                        'tipos_archivo' => 'pdf',         'obligatorio' => true,  'orden' => 3],
            ['slug' => 'ine',               'nombre' => 'INE',                         'tipos_archivo' => 'pdf,jpg,png', 'obligatorio' => false, 'orden' => 4],
        ];

        foreach ($basicos as $doc) {
            $exists = DB::table('document_requirements')
                ->where('institution_id', $institutionId)
                ->where('proceso', 'inscripcion')
                ->where('slug', $doc['slug'])
                ->whereNull('deleted_at')
                ->exists();

            if (! $exists) {
                DB::table('document_requirements')->insert([
                    'institution_id' => $institutionId,
                    'proceso'        => 'inscripcion',
                    'nombre'         => $doc['nombre'],
                    'descripcion'    => null,
                    'tipos_archivo'  => $doc['tipos_archivo'],
                    'cantidad'       => 1,
                    'obligatorio'    => $doc['obligatorio'],
                    'orden'          => $doc['orden'],
                    'activo'         => true,
                    'slug'           => $doc['slug'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('document_requirements')
            ->whereIn('slug', ['acta_nacimiento', 'certificado_prepa', 'curp', 'ine'])
            ->delete();

        Schema::table('document_requirements', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
