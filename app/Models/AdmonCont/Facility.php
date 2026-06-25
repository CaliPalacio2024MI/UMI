<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_aula',
        'career_classification_id',
        'career_id',
        'tipo_materia',
    ];

    public function classification(): BelongsTo
    {
        return $this->belongsTo(CareerClassification::class, 'career_classification_id');
    }

    /** Legacy */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function careers(): BelongsToMany
    {
        return $this->belongsToMany(Career::class, 'facility_career');
    }

    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'facility_materia');
    }

    public function horariosClase(): HasMany
    {
        return $this->hasMany(HorarioClase::class, 'aula_id');
    }

    public function scopeOrderedForHorarios($query)
    {
        return $query->orderBy('nombre_aula');
    }
}