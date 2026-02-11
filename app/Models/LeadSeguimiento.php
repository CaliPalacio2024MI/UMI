<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadSeguimiento extends Model
{
    //
    protected $fillable = [
        'lead_id',
        'estado',
        'fecha',
        'hora'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
