<?php

namespace App\Models;

use App\Models\Users\Institution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentRequirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_requirements';

    protected $fillable = [
        'institution_id',
        'proceso',
        'nombre',
        'descripcion',
        'tipos_archivo',
        'cantidad',
        'obligatorio',
        'orden',
        'activo',
        'slug',
    ];

    protected $casts = [
        'cantidad'    => 'integer',
        'obligatorio' => 'boolean',
        'orden'       => 'integer',
        'activo'      => 'boolean',
    ];

    /**
     * Procesos disponibles (slug => etiqueta visible).
     */
    public const PROCESOS = [
        'inscripcion'             => 'Inscripción',
        'expediente_alumnos'      => 'Expediente de alumnos',
        'becas'                   => 'Becas',
        'titulacion'              => 'Titulación',
        'servicio_social'         => 'Servicio social',
        'practicas_profesionales' => 'Prácticas profesionales',
    ];

    /**
     * Tipos de archivo que se pueden solicitar (extensión => etiqueta visible).
     */
    public const TIPOS_ARCHIVO = [
        'pdf' => 'PDF',
        'jpg' => 'JPG / JPEG',
        'png' => 'PNG',
        'doc' => 'Word (DOC/DOCX)',
        'xls' => 'Excel (XLS/XLSX)',
    ];

    // --- RELACIONES ---

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function submissions()
    {
        return $this->hasMany(SubmittedDocument::class, 'document_requirement_id');
    }

    // --- ACCESORES / HELPERS ---

    /**
     * Etiqueta legible del proceso.
     */
    public function getProcesoLabelAttribute(): string
    {
        return self::PROCESOS[$this->proceso] ?? ucfirst(str_replace('_', ' ', (string) $this->proceso));
    }

    /**
     * Tipos de archivo como arreglo de extensiones.
     */
    public function tiposArchivoArray(): array
    {
        return collect(explode(',', (string) $this->tipos_archivo))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();
    }

    // --- SCOPES ---

    /**
     * Documentos configurados (activos) de un proceso, para una unidad de negocio.
     * Pensado para reflejar la configuración en los módulos que reciben la info.
     */
    public function scopeForProcess($query, int $institutionId, string $proceso)
    {
        return $query->where('institution_id', $institutionId)
                     ->where('proceso', $proceso)
                     ->where('activo', true)
                     ->orderBy('orden')
                     ->orderBy('id');
    }

    /**
     * Igual que forProcess pero resolviendo la unidad de negocio activa
     * (sesión, con fallback a la Universidad). Útil en los módulos receptores.
     */
    public function scopeForActiveProcess($query, string $proceso)
    {
        return $query->forProcess(self::resolveInstitutionId(), $proceso);
    }

    /**
     * Unidad de negocio activa; si no hay en sesión, cae a la Universidad.
     */
    public static function resolveInstitutionId(): int
    {
        $id = (int) (session('active_institution_id') ?: 0);
        if ($id > 0) {
            return $id;
        }
        return (int) (Institution::where('is_universidad', true)->value('id') ?? 4);
    }
}
