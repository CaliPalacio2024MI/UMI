<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicFormConfig extends Model
{
    protected $table = 'public_form_config';

    protected $fillable = ['seccion', 'campo', 'etiqueta', 'activo', 'obligatorio', 'orden'];

    protected $casts = ['activo' => 'boolean', 'obligatorio' => 'boolean', 'siempre_activo' => 'boolean'];

    // Devuelve un mapa campo => objeto para uso rápido en vistas
    public static function asMap(): array
    {
        return static::orderBy('orden')->get()->keyBy('campo')->toArray();
    }
}
