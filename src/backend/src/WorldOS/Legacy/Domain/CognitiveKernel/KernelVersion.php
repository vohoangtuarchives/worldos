<?php

namespace WorldOS\Legacy\Domain\CognitiveKernel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class KernelVersion extends Model
{
    use HasUuids;

    protected $fillable = [
        'version',
        'archetype_snapshot',
        'law_snapshot',
        'coupling_rules',
        'release_notes',
        'released_at'
    ];

    protected $casts = [
        'archetype_snapshot' => 'array',
        'law_snapshot' => 'array',
        'coupling_rules' => 'array',
        'released_at' => 'datetime',
    ];

    /**
     * Get the current active kernel version
     */
    public static function current(): ?self
    {
        return self::orderBy('released_at', 'desc')->first();
    }

    /**
     * Get kernel version by version string
     */
    public static function findByVersion(string $version): ?self
    {
        return self::where('version', $version)->first();
    }

    /**
     * Create a new kernel version snapshot
     */
    public static function createSnapshot(
        string $version,
        ?string $releaseNotes = null
    ): self {
        // Snapshot all archetypes
        $archetypes = Archetype::all()->map(function ($archetype) {
            return [
                'key' => $archetype->key,
                'domain' => $archetype->domain,
                'polarity' => $archetype->polarity,
                'baseline_weight' => $archetype->baseline_weight,
                'volatility' => $archetype->volatility,
                'description' => $archetype->description,
            ];
        })->toArray();

        // Snapshot all world laws (if they exist)
        $laws = [];
        if (class_exists('\\App\\Models\\WorldLaw')) {
            $laws = \App\Models\WorldLaw::all()->map(function ($law) {
                return [
                    'key' => $law->key,
                    'category' => $law->category,
                    'priority' => $law->priority,
                    'archetype_coupling' => $law->archetype_coupling ?? null,
                    'legitimacy_weight' => $law->legitimacy_weight ?? 0.5,
                ];
            })->toArray();
        }

        return self::create([
            'version' => $version,
            'archetype_snapshot' => $archetypes,
            'law_snapshot' => $laws,
            'coupling_rules' => self::defaultCouplingRules(),
            'release_notes' => $releaseNotes,
            'released_at' => now(),
        ]);
    }

    /**
     * Default coupling rules between archetypes and world systems
     */
    private static function defaultCouplingRules(): array
    {
        return [
            'legitimacy_formula' => 'archetype_weight * myth_intensity - inequality - trauma',
            'drift_threshold' => 0.1,
            'mutation_triggers' => [
                'EXTREME_COLLAPSE',
                'MYTH_PARADOX',
                'REPEATED_FAILURE'
            ],
        ];
    }
}
