<?php

namespace App\Models\Schoolar;

use Illuminate\Database\Eloquent\Model;

class Beca extends Model
{
    protected $table = 'becas';

    protected $fillable = [
        'institution_id',
        'nombre',
        'tipo',
        'especificaciones',
        'documentos_requeridos',
        'activo',
    ];

    protected $casts = [
        'documentos_requeridos' => 'array',
        'activo' => 'boolean',
    ];
}
