<?php

namespace App\Models\Schoolar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BecaAsignacion extends Model
{
    protected $table = 'beca_asignaciones';

    protected $fillable = [
        'user_id',
        'beca_id',
        'institution_id',
        'status',
        'notas',
        'assigned_by',
        'activated_at',
        'documentos_requeridos',
    ];

    protected $casts = [
        'activated_at'          => 'datetime',
        'documentos_requeridos' => 'array',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class, 'user_id');
    }

    public function beca(): BelongsTo
    {
        return $this->belongsTo(Beca::class, 'beca_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class, 'assigned_by');
    }
}
