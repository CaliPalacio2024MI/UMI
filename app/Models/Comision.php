<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Lead;

class Comision extends Model
{
    protected $table = 'comisiones';

    protected $fillable = [
        'ctp_id',    // <-- necesario
        'lead_id',   // <-- necesario
        'clasificacion',
        'producto',
        'precio',
        'porcentaje',
        'total'
    ];
}