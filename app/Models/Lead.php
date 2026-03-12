<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\User;

class Lead extends Model
{

    protected $fillable = [
        'tutor_curp',
        'tutor_nombre',
        'tutor_paterno',
        'tutor_materno',
        'telefono1',
        'telefono2',
        'tutor_email',

        'alumno_curp',
        'alumno_nombre',
        'alumno_paterno',
        'alumno_materno',
        
        'carrera_id',

        'origen',
        'clasificacion'
    ];
    public function seguimientos()
{
    return $this->hasMany(LeadSeguimiento::class);
}
public function ctp()
{
    return $this->belongsTo(User::class, 'ctp_id');
}
public function carrera()
{
    return $this->belongsTo(\App\Models\Carrera::class);
}

}