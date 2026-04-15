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
            'group_department'
        );
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function workstations()
    {
        return $this->belongsToMany(\App\Models\Users\Workstation::class);
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
    // Relación con participantes (opcional)
    public function participants()
    {
        return $this->belongsToMany(\App\Models\Users\User::class);
    }
}
