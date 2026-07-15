<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Users\User;

class Tarea extends Model
{
    protected $fillable = [
        'horario_clase_id',
        'created_by',
        'tipo',
        'titulo',
        'descripcion',
        'fecha_apertura',
        'fecha_vencimiento',
        'fecha_cierre',
        'puntaje_maximo',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function horarioClase(): BelongsTo
    {
        return $this->belongsTo(HorarioClase::class, 'horario_clase_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(TareaEntrega::class);
    }
}
