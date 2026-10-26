<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\User;
use App\Models\Users\Department;
use App\Models\Users\Workstation;
use App\Models\Cursos\CourseSession;

class Group extends Model
{
    protected $fillable = [
        'type',
        'min_participants',
        'max_participants',
        'institution_id',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\Cursos\Course::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'group_departments', 'group_id', 'department_id')
            ->withTimestamps();
    }

    public function workstations()
    {
        return $this->belongsToMany(Workstation::class, 'group_workstations', 'group_id', 'workstation_id')
            ->withTimestamps();
    }

    public function sessions()
    {
        return $this->belongsToMany(CourseSession::class, 'group_session', 'group_id', 'course_session_id');
    }

    /** Personas que forman la lista de participantes del grupo. */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'group_participants', 'group_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * La vista del curso, el QR y el PDF leen "hosts" (anfitriones).
     * Son los mismos participantes, así que se lee la misma tabla.
     */
    public function hosts()
    {
        return $this->participants();
    }
}
