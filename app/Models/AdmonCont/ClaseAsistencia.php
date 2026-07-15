<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users\User;

class ClaseAsistencia extends Model
{
    protected $fillable = [
        'horario_clase_id',
        'user_id',
        'fecha',
        'presente',
    ];

    protected $casts = [
        'fecha' => 'date',
        'presente' => 'boolean',
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
