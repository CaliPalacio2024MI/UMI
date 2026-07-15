<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users\User;

class TareaEntrega extends Model
{
    protected $fillable = [
        'tarea_id',
        'user_id',
        'fecha_entrega',
        'puntaje_obtenido',
    ];

    protected $casts = [
        'fecha_entrega' => 'datetime',
    ];

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class);
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
