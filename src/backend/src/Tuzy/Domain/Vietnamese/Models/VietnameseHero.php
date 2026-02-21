<?php

namespace Tuzy\Domain\Vietnamese\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Jobs\RecalculateHeroScoreJob;

class VietnameseHero extends Model
{
    use HasUuids;

    protected $table = 'vietnamese_heroes';

    protected $fillable = [
        'name', 'birth_year', 'death_year', 'era', 'period', 'region',
        'archetype', 'cosmic_role', 'biography', 'quote',
        'military', 'governance', 'territory', 'philosophy', 'education',
        'culture', 'spirituality', 'rebellion', 'reform', 'diplomacy',
        'economic', 'mythic', 'impact_score',
        'scoring_version_id', 'last_scored_at'
    ];

    protected $casts = [
        'birth_year' => 'integer',
        'death_year' => 'integer',
        'era' => 'integer',
        'military' => 'float',
        'governance' => 'float',
        'territory' => 'float',
        'philosophy' => 'float',
        'education' => 'float',
        'culture' => 'float',
        'spirituality' => 'float',
        'rebellion' => 'float',
        'reform' => 'float',
        'diplomacy' => 'float',
        'economic' => 'float',
        'mythic' => 'float',
        'impact_score' => 'float',
        'last_scored_at' => 'datetime',
    ];

    // Relationships
    public function events(): HasMany
    {
        return $this->hasMany(HeroEvent::class, 'hero_id');
    }

    public function scoringVersion(): BelongsTo
    {
        return $this->belongsTo(ScoringVersion::class);
    }

    // Dimension scores as array
    public function getDimensionsAttribute(): array
    {
        return [
            'military' => $this->military,
            'governance' => $this->governance,
            'territory' => $this->territory,
            'philosophy' => $this->philosophy,
            'education' => $this->education,
            'culture' => $this->culture,
            'spirituality' => $this->spirituality,
            'rebellion' => $this->rebellion,
            'reform' => $this->reform,
            'diplomacy' => $this->diplomacy,
            'economic' => $this->economic,
            'mythic' => $this->mythic,
        ];
    }

    // Top 3 dimensions
    public function getTopDimensionsAttribute(): array
    {
        $dims = $this->dimensions;
        arsort($dims);
        return array_slice($dims, 0, 3, true);
    }

    // Scopes
    public function scopeByEra($query, int $era)
    {
        return $query->where('era', $era);
    }

    public function scopeByPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    public function scopeTopImpact($query, int $limit = 10)
    {
        return $query->orderBy('impact_score', 'desc')->limit($limit);
    }

    public function scopeStrongIn($query, string $dimension, float $threshold = 0.7)
    {
        return $query->where($dimension, '>=', $threshold);
    }

    // Trigger recalculation
    public function recalculateScores(?string $versionId = null): void
    {
        dispatch(new RecalculateHeroScoreJob($this->id, $versionId));
    }

    /**
     * Check if this hero can trigger a timeline bifurcation
     */
    public function canTriggerBifurcation(): bool
    {
        $bifurcationArchetypes = [
            'INDEPENDENCE_HERO',
            'REVOLUTIONARY_LEADER',
            'UNIFIER_EMPEROR',
            'INDEPENDENCE_BIFURCATION',
        ];
        
        if (!in_array($this->archetype, $bifurcationArchetypes)) {
            return false;
        }
        
        // Check if hero has sufficient impact
        return $this->impact_score >= 0.7;
    }
}
