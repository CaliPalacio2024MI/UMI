<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Users\Career;
use App\Models\Users\User;

class Lead extends Model
{

    protected $fillable = [
        'tutor_curp',
        'tutor_nombre',
        'tutor_paterno',
        'tutor_materno',
        'telefono1',
        'telefono2',
        'tutor_email',

        'alumno_curp',
        'alumno_nombre',
        'alumno_paterno',
        'alumno_materno',
        
        'carrera_id',
        'semestre',

        'origen',
        'clasificacion',

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
    ];

    protected $casts = [
        'doc_acta_rechazado' => 'boolean',
        'doc_certificado_rechazado' => 'boolean',
        'doc_curp_rechazado' => 'boolean',
        'doc_ine_rechazado' => 'boolean',
        'doc_ficha_pago_rechazado' => 'boolean',
        'doc_factura_xml_rechazado' => 'boolean',
    ];
    public function seguimientos()
{
    return $this->hasMany(LeadSeguimiento::class);
}
public function ctp()
{
    return $this->belongsTo(User::class, 'ctp_id');
}
public function carrera()
{
    return $this->belongsTo(Career::class, 'carrera_id');
}

    /**
     * Leads cuyo seguimiento más reciente (mayor id) tiene el estado indicado.
     * Coincide con la lógica del CRM (último seguimiento).
     */
    public function scopeWhereUltimoSeguimientoEs(Builder $query, string $estado): Builder
    {
        return $query->whereExists(function ($q) use ($estado) {
            $q->select(DB::raw(1))
                ->from('lead_seguimientos as ls')
                ->whereColumn('ls.lead_id', 'leads.id')
                ->where('ls.estado', $estado)
                ->whereRaw('ls.id = (SELECT MAX(id) FROM lead_seguimientos WHERE lead_id = leads.id)');
        });
    }

    /**
     * Base de datos compartida con Control Escolar → lista de alumnos (filas CRM):
     * último seguimiento Aspirante y sin duplicar CURP ya existente en users.
     */
    public static function queryBaseAspirantesControlEscolar(): Builder
    {
        $q = static::query()->whereUltimoSeguimientoEs('Aspirante');

        $curps = User::query()
            ->whereNotNull('curp')
            ->where('curp', '!=', '')
            ->pluck('curp');
        if ($curps->isNotEmpty()) {
            $q->where(function ($w) use ($curps) {
                $w->whereNull('alumno_curp')
                    ->orWhere('alumno_curp', '')
                    ->orWhereNotIn('alumno_curp', $curps->all());
            });
        }

        return $q;
    }

}