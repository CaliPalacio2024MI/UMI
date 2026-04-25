<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CoursePeriod extends Model
{
    protected $fillable = [
        'course_id',
        'name',
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
     * Scope para períodos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para período actual (hoy)
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('is_active', true)
                     ->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }
}