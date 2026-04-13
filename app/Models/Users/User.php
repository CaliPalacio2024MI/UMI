<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

// Relaciones
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Faltaba importar esto

// Modelos
use App\Models\Facturacion\Billing; // Faltaba importar esto
use App\Models\Users\Role;
use App\Models\Users\Institution;
use App\Models\Users\Address;
use App\Models\Users\AcademicProfile;
use App\Models\Users\CorporateProfile;
use App\Models\Cursos\Course;
use App\Models\Cursos\Completion;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\HorarioClaseOculta;
use App\Models\Users\Department;
use App\Models\Users\Workstation;

/**
 * @property int $id
 * @property string $nombre
 * @property string $apellido_paterno
 * @property string $apellido_materno
 * @property string $email
 * @property string $password
 * @property string $RFC
 * @property string|null $telefono
 * @property string|null $fecha_nacimiento
 * @property int|null $edad
 * @property int|null $address_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read AcademicProfile|null $academicProfile
 * @property-read Address|null $address
 * @property-read CorporateProfile|null $corporateProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Institution> $institutions
 * @property-read int|null $institutions_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereApellidoMaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereApellidoPaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEdad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFechaNacimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRFC($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */

class User extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable;

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'RFC',
        'curp',
        'telefono',
        'fecha_nacimiento',
        'edad',
        'is_active',
        'address_id',
        'institution_id',
        'department_id',
        'workstation_id',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // --- TRUCO DE COMPATIBILIDAD PARA LARAVEL ---
    // Laravel a veces busca 'name' internamente. Esto evita errores raros.
    public function getNameAttribute()
    {
        return "{$this->nombre} {$this->apellido_paterno}";
    }

    // --- RELACIONES ---

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }
    public function groups()
    {
       return $this->belongsToMany(\App\Models\Group::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles_institution', 'user_id', 'role_id')
                    ->withPivot('institution_id', 'is_active')
                    ->withTimestamps();
    }

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_user');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function academicProfile(): HasOne
    {
        return $this->hasOne(AcademicProfile::class);
    }

    /** Carreras en las que imparte un docente (tabla pivote career_user). */
    public function teachingCareers(): BelongsToMany
    {
        return $this->belongsToMany(Career::class, 'career_user', 'user_id', 'career_id');
    }

    /**
     * IDs de carrera separados por comas (p. ej. filtro de docentes en horarios por carrera).
     */
    public function teachingCareerIdsCsv(): string
    {
        if ($this->relationLoaded('teachingCareers') && $this->teachingCareers->isNotEmpty()) {
            return $this->teachingCareers->pluck('id')->unique()->sort()->values()->implode(',');
        }
        if ($this->exists) {
            $ids = $this->teachingCareers()->pluck('id');
            if ($ids->isNotEmpty()) {
                return $ids->map(fn ($id) => (int) $id)->unique()->sort()->values()->implode(',');
            }
        }
        if ($this->academicProfile?->career_id) {
            return (string) (int) $this->academicProfile->career_id;
        }

        return '';
    }

    public function corporateProfile(): HasOne
    {
        return $this->hasOne(CorporateProfile::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user');
        return $this->belongsToMany(Course::class)->withPivot('progress', 'started_at');
    }

    /** Clases (horarios) en las que el alumno está inscrito. */
    public function horarioClases()
    {
        return $this->belongsToMany(HorarioClase::class, 'horario_clase_user', 'user_id', 'horario_clase_id')
            ->withTimestamps();
    }

    /** Clases que este usuario (control académico) marcó como guardadas/ocultas en el módulo Clases. */
    public function horarioClaseOcultas(): HasMany
    {
        return $this->hasMany(HorarioClaseOculta::class);
    }

    public function completions()
    {
        return $this->hasMany(Completion::class);
    }

    // --- FUNCIONES AUXILIARES ---

    public function hasActiveRole(string $roleName): bool
    {
        return session('active_role_name') === $roleName;
    }

    public function hasAnyActiveRole(array $roles): bool
    {
        return in_array(session('active_role_name'), $roles);
    }

    public function hasRole(string $roleName): bool
    {
        return DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->where('roles.name', $roleName)
        ->where('user_roles_institution.is_active', true)
        ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->whereIn('roles.name', $roles)
        ->where('user_roles_institution.is_active', true)
        ->exists();
    }

    public function getAvailableRoles(): array
    {
        $contexts = [];

       $userContexts = DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->join('institutions', 'user_roles_institution.institution_id', '=', 'institutions.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->where('user_roles_institution.is_active', true)
        ->select(
            'institutions.id as institution_id',
            'institutions.name as institution_name',
            'institutions.logo_path',
            'roles.id as role_id',
            'roles.name as role_name',
            'roles.display_name',
            'user_roles_institution.is_active'
        )
        ->get();

        $seen = [];
        foreach ($userContexts as $context) {

            $key = $context->institution_id . '_' . $context->role_id;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $contexts[] = (array) $context;
        }

        return $contexts;
    }

    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    public function rolesInInstitution($institutionId)
    {
        return $this->roles()
            ->wherePivot('institution_id', $institutionId)
            ->get();
    }

    /**
     * Texto del rol a mostrar en Ajustes → Usuarios cuando hay varios roles en la misma institución.
     * Prioriza el rol guardado en users.role_id; si no aplica, ordena por importancia (p. ej. CTP antes que Docente).
     */
    public function roleDisplayNameForAjustes(?int $institutionId = null): string
    {
        $institutionId = $institutionId ?? (int) session('active_institution_id', 0);
        if ($institutionId <= 0) {
            return 'Sin Rol';
        }

        $roles = $this->relationLoaded('roles')
            ? $this->roles->filter(fn ($r) => (int) ($r->pivot->institution_id ?? 0) === $institutionId)
            : $this->roles()->wherePivot('institution_id', $institutionId)->get();

        if ($roles->isEmpty()) {
            return 'Sin Rol';
        }

        if ($roles->count() === 1) {
            return (string) $roles->first()->display_name;
        }

        // Varias asignaciones en la misma institución: no usar el primero al azar ni un role_id desactualizado.
        $priority = [
            'master' => 100,
            'ctp' => 95,
            'coordinador_ctp' => 93,
            'gerente_capacitacion' => 90,
            'control_administrativo' => 85,
            'control_escolar' => 80,
            'docente' => 50,
            'estudiante' => 45,
            'anfitrion' => 40,
        ];

        $chosen = $roles->sortByDesc(fn ($r) => $priority[$r->name] ?? 0)->first();

        return $chosen ? (string) $chosen->display_name : 'Sin Rol';
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function workstation(): BelongsTo
    {
        return $this->belongsTo(Workstation::class, 'workstation_id');
    }
}
