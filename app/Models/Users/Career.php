<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Institution;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar

// Modelos necesarios para la consulta y relaciones
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\Horario;


/**
 * @property int $id
 * @property string $name
 * @property int $institution_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Institution $institution
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereUpdatedAt($value)
 * @mixin \Eloquent
 */


namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Users\Institution;
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\Horario;

class Career extends Model
{
    protected $fillable = [
        'official_id',
        'name',
        'description1',
        'description2',
        'description3',
        'type',
        'semesters', // <--- ✅ CORRECTO: Plural, como en tu BD
        'institution_id', // Agregado por seguridad ya que está en tu BD
        'career_classification_id',
        'credits',         // Agregado por seguridad
        'pricing_mode',
        'monthly_prices',
        'porcentaje_cargo_moratorio',
        'monto_mensualidad',
        'cargo_monetario',
        'fecha_vencimiento_moratorio',
        'visible_landing'
    ];

    protected $casts = [
        'monthly_prices' => 'array',
        'porcentaje_cargo_moratorio' => 'decimal:2',
        'monto_mensualidad' => 'decimal:2',
        'cargo_monetario' => 'decimal:2',
        'fecha_vencimiento_moratorio' => 'date',
    ];

    // --- RELACIONES ---

    public function institution(): BelongsTo 
    { 
        return $this->belongsTo(Institution::class); 
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(CareerClassification::class, 'career_classification_id');
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }
    
    public function academicProfiles(): HasMany
    {
        return $this->hasMany(AcademicProfile::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}