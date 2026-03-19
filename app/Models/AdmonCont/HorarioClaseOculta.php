<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users\User;

/**
 * Clases que el usuario de control académico marcó como "guardadas" en la cajita (Clases).
 * Se usan para ocultar esas filas de la tabla izquierda. Persistido en BD desde sesión.
 */
class HorarioClaseOculta extends Model
{
    protected $table = 'horario_clase_ocultas';

    protected $fillable = ['user_id', 'horario_clase_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function horarioClase(): BelongsTo
    {
        return $this->belongsTo(HorarioClase::class);
    }
}
