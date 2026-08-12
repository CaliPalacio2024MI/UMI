<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormularioCampo extends Model
{
    protected $table = 'formulario_campos';

    protected $fillable = [
        'etiqueta',
        'nombre_campo',
        'tipo',
        'opciones',
        'placeholder',
        'obligatorio',
        'valoracion',
        'es_nombre',
        'demografica',
        'activo',
        'orden',
        'seccion',
        'despues_de',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'valoracion'  => 'boolean',
        'es_nombre'   => 'boolean',
        'demografica' => 'boolean',
        'activo'      => 'boolean',
    ];

    public function opcionesArray(): array
    {
        if (empty($this->opciones)) return [];
        return array_filter(array_map('trim', explode("\n", $this->opciones)));
    }
}
