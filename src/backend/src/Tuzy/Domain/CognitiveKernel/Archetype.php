<?php

namespace Tuzy\Domain\CognitiveKernel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Archetype extends Model
{
    use HasUuids, Traits\ConstitutionalInvariants;

    protected $fillable = [
        'key',
        'domain',
        'polarity',
        'baseline_weight',
        'volatility',
        'version',
        'description'
    ];

    protected $casts = [
        'polarity' => 'array',
        'baseline_weight' => 'float',
        'volatility' => 'float',
    ];

    /**
     * Archetype domains
     */
    public const DOMAIN_PERCEPTION = 'perception';
    public const DOMAIN_POWER = 'power';
    public const DOMAIN_SOCIAL = 'social';
    public const DOMAIN_METAPHYSICAL = 'metaphysical';

    /**
     * Get all archetypes for a specific domain
     */
    public static function forDomain(string $domain): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('domain', $domain)->get();
    }

    /**
     * Get archetype by key
     */
    public static function findByKey(string $key): ?self
    {
        return self::where('key', $key)->first();
    }

    /**
     * Check if archetype supports a polarity
     */
    public function supportsPolarity(string $polarity): bool
    {
        return in_array($polarity, $this->polarity);
    }

    /**
     * Get mutations that originated from this archetype
     */
    public function mutations()
    {
        return $this->hasMany(ArchetypeMutation::class, 'parent_archetype', 'key');
    }
}
