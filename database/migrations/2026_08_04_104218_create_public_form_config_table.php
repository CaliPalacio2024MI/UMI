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
        Schema::create('public_form_config', function (Blueprint $table) {
            $table->id();
            $table->enum('seccion', ['tutor', 'postulante']);
            $table->string('campo', 60)->unique();
            $table->string('etiqueta', 120);
            $table->boolean('activo')->default(true);
            $table->boolean('obligatorio')->default(true);
            $table->boolean('siempre_activo')->default(false); // no se puede desactivar
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });

        // Sembrar los campos base
        DB::table('public_form_config')->insert([
            // Tutor
            ['seccion'=>'tutor',      'campo'=>'tutor_curp',    'etiqueta'=>'CURP',              'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>1, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'tutor',      'campo'=>'tutor_nombre',  'etiqueta'=>'Nombre(s)',          'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>2, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'tutor',      'campo'=>'tutor_paterno', 'etiqueta'=>'Apellido paterno',   'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>3, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'tutor',      'campo'=>'tutor_materno', 'etiqueta'=>'Apellido materno',   'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>4, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'tutor',      'campo'=>'telefono1',     'etiqueta'=>'Teléfono 1',         'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>5, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'tutor',      'campo'=>'telefono2',     'etiqueta'=>'Teléfono 2',         'activo'=>1,'obligatorio'=>0,'siempre_activo'=>0,'orden'=>6, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'tutor',      'campo'=>'tutor_email',   'etiqueta'=>'Correo electrónico', 'activo'=>1,'obligatorio'=>0,'siempre_activo'=>0,'orden'=>7, 'created_at'=>now(),'updated_at'=>now()],
            // Postulante
            ['seccion'=>'postulante', 'campo'=>'alumno_curp',   'etiqueta'=>'CURP',              'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>1, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'postulante', 'campo'=>'alumno_nombre', 'etiqueta'=>'Nombre(s)',          'activo'=>1,'obligatorio'=>1,'siempre_activo'=>1,'orden'=>2, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'postulante', 'campo'=>'alumno_paterno','etiqueta'=>'Apellido paterno',   'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>3, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'postulante', 'campo'=>'alumno_materno','etiqueta'=>'Apellido materno',   'activo'=>1,'obligatorio'=>1,'siempre_activo'=>0,'orden'=>4, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'postulante', 'campo'=>'nivel_educativo','etiqueta'=>'Nivel educativo',   'activo'=>1,'obligatorio'=>0,'siempre_activo'=>0,'orden'=>5, 'created_at'=>now(),'updated_at'=>now()],
            ['seccion'=>'postulante', 'campo'=>'carrera_id',    'etiqueta'=>'Plan de estudio / Carrera','activo'=>1,'obligatorio'=>1,'siempre_activo'=>1,'orden'=>6,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_form_config');
    }
};
