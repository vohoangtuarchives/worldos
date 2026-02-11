<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Institution extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'charter_values' => 'array',
        'public_trust' => 'float',
        'authority_level' => 'float',
        'created_tick' => 'integer',
    ];

    public function actions()
    {
        return $this->hasMany(InstitutionalAction::class);
    }

    public function healingEvents()
    {
        return $this->hasMany(HealingEvent::class);
    }
}
