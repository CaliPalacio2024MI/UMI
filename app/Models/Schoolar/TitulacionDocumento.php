<?php

namespace App\Models\Schoolar;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TitulacionDocumento extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'titulacion_documentos';

    protected $fillable = [
        'user_id',
        'nombre_documento',
        'descripcion',
        'archivo_path',
        'mime_type',
        'tamano_bytes',
        'uploaded_by',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
