<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domains\Cosmology\Entities\WorldScar;

class World extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'type',
        'description',
        'law_profile',
        'config',
        'tags',
        'parent_id',
        'status',
        'health_status',
        'tick',
        'genre',
        'preset',
        'gene_vector',
        'physics_profile',
        'current_time',
        'calendar_system',
        'entropy',
        'current_tick',
        'autonomous', // Added for simulation control
        
        // Vietnamese Origin & Yggdrasil Multiverse
        'origin_type',
        'origin_metadata',
        'initial_entropy',
        'initial_energy',
        'initial_stability',
        'cosmic_energy',
        'cosmic_entropy',
        'cosmic_stability',
        'yggdrasil_realm',
        'current_era',
        'bifurcation_era',
        'bifurcation_type',
        'bifurcation_trigger',
        
        // Lineage & Prophet
        'lineage_root_id',
        'generation',
        'is_prophet',
        'prophet_metadata',
    ];

    protected $casts = [
        'law_profile' => 'array',
        'physics_profile' => 'array', // Will be cast to PhysicsProfile via accessor
        'config' => 'array',
        'tags' => 'array',
        'gene_vector' => 'array',
        'autonomous' => 'boolean',
        'health_status' => \App\Domains\World\Enums\WorldHealthStatus::class,
        'type' => \App\Domains\World\Enums\WorldType::class,
        
        // Vietnamese Origin & Yggdrasil
        'origin_metadata' => 'array',
        'initial_entropy' => 'float',
        'initial_energy' => 'float',
        'initial_stability' => 'float',
        'cosmic_energy' => 'float',
        'cosmic_entropy' => 'float',
        'cosmic_stability' => 'float',
        'current_era' => 'integer',
        'bifurcation_era' => 'integer',
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

    public function cosmicSnapshots(): HasMany
    {
        return $this->hasMany(CosmicSnapshot::class)->orderBy('year');
    }

    public function cosmicEvents(): HasMany
    {
        return $this->hasMany(CosmicEvent::class)->orderBy('year');
    }

    public function getPhysicsProfileAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        
        return $value 
            ? \App\Domains\World\ValueObjects\PhysicsProfile::fromArray($value) 
            : \App\Domains\World\ValueObjects\PhysicsProfile::standard();
    }

    public function setPhysicsProfileAttribute($value)
    {
        if ($value instanceof \App\Domains\World\ValueObjects\PhysicsProfile) {
            $this->attributes['physics_profile'] = json_encode($value->toArray());
        } else {
            $this->attributes['physics_profile'] = is_array($value) ? json_encode($value) : $value;
        }
    }

    public function getTickAttribute(): int
    {
        return (int)($this->attributes['current_tick'] ?? 0);
    }

    /**
     * Check if the world is running autonomously (auto-simulating).
     */
    public function isAutonomous(): bool
    {
        return (bool)($this->attributes['autonomous'] ?? false);
    }

    // Lineage Relationships
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(World::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(World::class, 'parent_id');
    }

    public function lineageRoot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(World::class, 'lineage_root_id');
    }

    public function descendants(): HasMany
    {
        return $this->hasMany(World::class, 'lineage_root_id');
    }

    public function state(): HasOne
    {
        return $this->hasOne(WorldState::class);
    }
}
