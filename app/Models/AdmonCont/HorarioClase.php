<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\AdmonCont\HorarioFranja;
use App\Models\Users\Career;
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\Facility;
use App\Models\Users\User;

class HorarioClase extends Model
{
    //
    protected $fillable = [
        'materia_id',
        'career_id',
        'user_id',
        'aula_id'
    ];

    public function franjas()
    {
        return $this->hasMany(HorarioFranja::class);
    }
    public function carrera(): BelongsTo
    {
        // Asegúrate de que 'carrera_id' sea el nombre de la columna FK en tu tabla 'horarios_clases'
        return $this->belongsTo(Career::class, 'career_id'); 
    }
    public function materia(): BelongsTo
    {
        // El nombre de la columna FK en horarios_clases es 'materia_id'
        return $this->belongsTo(Materia::class, 'materia_id'); 
    }
    public function user(): BelongsTo
    {
        // La columna FK en horarios_clases es 'user_id'
        return $this->belongsTo(User::class, 'user_id'); 
    }
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'aula_id');
    }

    /**
     * Alumnos inscritos en esta clase (pivot horario_clase_user).
     */
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'horario_clase_user', 'horario_clase_id', 'user_id')
            ->withTimestamps();
    }
}
