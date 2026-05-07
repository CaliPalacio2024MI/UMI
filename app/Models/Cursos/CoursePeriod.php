<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CoursePeriod extends Model
{
    protected $fillable = [
        'course_id',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Usuarios asignados a este período
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user', 'period_id', 'user_id')
                    ->wherePivot('course_id', $this->course_id);
    }

    /**
     * Verificar si el período está activo HOY
     */
    public function isCurrentlyActive()
    {
        $today = Carbon::today();
        return $this->is_active 
            && $today->greaterThanOrEqualTo($this->start_date) 
            && $today->lessThanOrEqualTo($this->end_date);
    }

    /**
     * Obtener estado del período
     */
    public function getStatusAttribute()
    {
        $today = Carbon::today();
        
        if ($today->lessThan($this->start_date)) {
            return 'No iniciado';
        } elseif ($today->greaterThan($this->end_date)) {
            return 'Finalizado';
        } else {
            return 'Activo';
        }
    }

    /**
     * Obtener color del estado
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'Activo':
                return '#28a745';
            case 'Finalizado':
                return '#dc3545';
            default:
                return '#6c757d';
        }
    }
}