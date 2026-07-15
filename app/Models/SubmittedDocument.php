<?php

namespace App\Models;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmittedDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'submitted_documents';

    protected $fillable = [
        'document_requirement_id',
        'user_id',
        'archivo_path',
        'nombre_original',
        'mime_type',
        'tamano_bytes',
        'uploaded_by',
    ];

    public function requirement()
    {
        return $this->belongsTo(DocumentRequirement::class, 'document_requirement_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
