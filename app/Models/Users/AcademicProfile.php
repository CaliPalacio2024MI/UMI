<?php

namespace App\Models\Users;

use App\Models\Users\Career;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'campus',
        'semestre',
        'status',
        'career_id',      
        'departamento',   
        'is_anfitrion',    
        'doc_acta_nacimiento',
        'doc_certificado_prepa',
        'doc_curp',
        'doc_ine',
        'doc_ficha_pago',
        'doc_factura_xml',
        'doc_acta_rechazado',
        'doc_certificado_rechazado',
        'doc_curp_rechazado',
        'doc_ine_rechazado',
        'doc_ficha_pago_rechazado',
        'doc_factura_xml_rechazado',
        'doc_comprobante_domicilio',
        'modules',
        'rol',
        'matricula',
        'documentoSEP_path',
        'ultimo_periodo_id',
        'foto'
    ];

    protected $casts = [
        'modules' => 'array',
        'is_anfitrion' => 'boolean', // <--- Útil para que te devuelva true/false
        'doc_acta_rechazado' => 'boolean',
        'doc_certificado_rechazado' => 'boolean',
        'doc_curp_rechazado' => 'boolean',
        'doc_ine_rechazado' => 'boolean',
        'doc_ficha_pago_rechazado' => 'boolean',
        'doc_factura_xml_rechazado' => 'boolean',
    ];

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // CORREGIDO: Usar 'career_id' para que coincida con la BD y el fillable
    public function career(): BelongsTo
{
    return $this->belongsTo(Career::class, 'career_id');
}
}