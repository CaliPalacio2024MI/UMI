<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';

    protected $fillable = [
        'tutor_nombre',
        'tutor_paterno',
        'tutor_materno',
        'telefono1',
        'telefono2',
        'alumno_nombre',
        'alumno_paterno',
        'alumno_materno',
        'rfc',
        'curp',
        'origen',
        'clasificacion'
    ];
}
