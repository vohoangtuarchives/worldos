<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class World extends Model
{
    protected $fillable = ['name', 'type', 'description', 'law_profile', 'config', 'tags', 'parent_id', 'status', 'health_status', 'tick', 'genre'];

    protected $casts = [
        'law_profile' => 'array',
        'config' => 'array',
        'tags' => 'array',
        'health_status' => \App\Domains\World\Enums\WorldHealthStatus::class,
        'type' => \App\Domains\World\Enums\WorldType::class,
    ];

    public function getLawProfileAttribute($value): \App\Domains\World\ValueObjects\WorldLawProfile
    {
        // If null or array, handle conversion
        if (is_string($value)) $value = json_decode($value, true);
        
        return $value 
            ? \App\Domains\World\ValueObjects\WorldLawProfile::fromArray($value)
            : \App\Domains\World\ValueObjects\WorldLawProfile::default();
    }

    public function setLawProfileAttribute(\App\Domains\World\ValueObjects\WorldLawProfile $profile): void
    {
        $this->attributes['law_profile'] = json_encode($profile->toArray());
    }

    public function clock(): HasOne
    {
        return $this->hasOne(WorldClock::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorldEvent::class);
    }

    public function scars(): HasMany
    {
        return $this->hasMany(WorldScar::class);
    }

    public function beliefs(): HasMany
    {
        return $this->hasMany(WorldBelief::class);
    }

    public function myths(): HasMany
    {
        return $this->hasMany(WorldMyth::class);
    }

    public function factions(): HasMany
    {
        return $this->hasMany(Faction::class);
    }

    public function powerProfile(): HasOne
    {
        return $this->hasOne(WorldPowerProfile::class);
    }
}
