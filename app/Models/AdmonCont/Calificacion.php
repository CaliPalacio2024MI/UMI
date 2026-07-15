<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users\User;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = [
        'horario_clase_id',
        'user_id',
        'period_id',
        'parcial',
        'calificacion',
        'confirmada',
    ];

    protected $casts = [
        'confirmada' => 'boolean',
    ];

    public function horarioClase(): BelongsTo
    {
        return $this->belongsTo(HorarioClase::class, 'horario_clase_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
