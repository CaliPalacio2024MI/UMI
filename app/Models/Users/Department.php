<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Institution;
use App\Models\Users\Workstation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model{
    use HasFactory;

    protected $fillable = [
        'name',
        'institution_id',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function institutions()
    {
        return $this->belongsToMany(
            Institution::class,
            'department_institution',
            'department_id',
            'institution_id'
        )->withTimestamps();
    }

    public function workstations()
    {
        return $this->hasMany(Workstation::class);
    }
}
