<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerClassification extends Model
{
    protected $fillable = [
        'name',
        'institution_id',
        'visible_landing',
        'is_active',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }
}
