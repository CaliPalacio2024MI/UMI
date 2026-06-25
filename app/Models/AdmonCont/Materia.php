<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar

// Modelos necesarios para la consulta y relaciones
use App\Models\Users\Career;


class Materia extends Model
{
    //
    protected $table = 'materias';

    protected $fillable = [
        'nombre',
        'clave',
        'creditos',
        'career_id',
        'descripcion',
        'objetivo',
        'temario',
        'infografia',
        'type',
        'semestre',
        'career_classification_id'
    ];
    protected $casts = [
    'temario' => 'array',
];
   public function career(): BelongsTo
{
    return $this->belongsTo(Career::class);
}

   public function classification(): BelongsTo
{
    return $this->belongsTo(\App\Models\Users\CareerClassification::class, 'career_classification_id');
}
    
}
