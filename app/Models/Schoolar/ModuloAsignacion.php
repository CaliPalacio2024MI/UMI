<?php

namespace App\Models\Schoolar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuloAsignacion extends Model
{
    protected $table = 'modulo_asignaciones';

    protected $fillable = [
        'user_id',
        'institution_id',
        'tipo',
        'status',
        'notas',
        'assigned_by',
        'activated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class, 'user_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class, 'assigned_by');
    }
}
