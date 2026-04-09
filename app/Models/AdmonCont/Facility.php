<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_aula',
        'seccion',
        'capacidad',
        'ubicacion',
        'tipo',
    ];

    public function horariosClase(): HasMany
    {
        return $this->hasMany(HorarioClase::class, 'aula_id');
    }

    /** Aulas ordenadas para selects del módulo Horarios. */
    public function scopeOrderedForHorarios($query)
    {
        return $query->orderBy('numero_aula')->orderBy('id');
    }
}
