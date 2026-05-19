<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\User;

class Group extends Model
{
    protected $fillable = [
        'type',
        'min_participants',
        'max_participants',
        'institution_id'
    ];
public function departments()
{
    return $this->belongsToMany(
        \App\Models\Users\Department::class,
        'group_departments',
        'group_id',
        'department_id'
    );
}
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function hosts()
    {
        return $this->belongsToMany(User::class, 'group_host', 'group_id', 'host_id');
    }
public function workstations()
{
    return $this->belongsToMany(
        \App\Models\Users\Workstation::class,
        'group_workstations',
        'group_id',
        'workstation_id'
    );
}

    public function sessions()
    {
        return $this->belongsToMany(
            \App\Models\Cursos\CourseSession::class,
            'group_session',
            'group_id',
            'course_session_id'
        );
    }
    // Relación con participantes
public function participants()
{
    return $this->belongsToMany(
        \App\Models\Users\User::class,
        'group_participants',
        'group_id',
        'user_id'
    );
}
}
