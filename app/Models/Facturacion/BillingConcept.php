<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingConcept extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla en BD
    protected $table = 'billing_concepts'; 

    protected $fillable = [
        'institution_id',
        'concept',
        'amount',
        'porcentaje_cargo_moratorio',
        'cargo_monetario',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount'    => 'decimal:2',
        'porcentaje_cargo_moratorio' => 'decimal:2',
        'cargo_monetario' => 'decimal:2',
    ];
}