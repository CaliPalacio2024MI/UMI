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
    public function workstations()
    {
        return $this->belongsToMany(\App\Models\Users\Workstation::class);
    }

    // Relación con participantes (opcional)
    public function participants()
    {
        return $this->belongsToMany(\App\Models\Users\User::class);
    }
}
