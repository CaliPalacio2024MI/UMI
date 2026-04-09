<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
    'day',
    'start_time',
    'end_time',
    'classroom'
    ];
    public function courses()
    {
        return $this->belongsToMany(\App\Models\Course::class);
    }
}
