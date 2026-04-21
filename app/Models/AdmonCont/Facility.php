<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Users\Career;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_aula',
        'career_id',
        'tipo_materia',
    ];

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function horariosClase(): HasMany
    {
        return $this->hasMany(HorarioClase::class, 'aula_id');
    }

    /** Aulas ordenadas para selects del módulo Horarios. */
    public function scopeOrderedForHorarios($query)
    {
        return $query->orderBy('nombre_aula')->orderBy('id');
    }
}
