<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Course;



class CourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'date',
        'start_time',
        'end_time',
        'attendance_enabled',
        'qr_token'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function groups()
    {
    return $this->belongsToMany(
        Group::class,
        'group_session',
        'course_session_id',
        'group_id'
    );
    }

}
