<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialSeed extends Model
{
    protected $fillable = [
        'world_state_id',
        'seed_type',
        'source_axes',
        'content',
        'relevance_score',
        'tension_level',
        'archetype',
    ];

    protected $casts = [
        'content' => 'json',
        'relevance_score' => 'decimal:2',
        'tension_level' => 'decimal:2',
    ];

    public function worldState(): BelongsTo
    {
        return $this->belongsTo(WorldState::class);
    }

    /**
     * Generate conflict seed from axis collision
     */
    public static function fromAxisCollision(WorldState $world, array $collision): self
    {
        $content = [
            'conflict' => self::generateConflict($collision),
            'characters' => self::generateCharacters($collision),
            'setting' => self::generateSetting($world, $collision),
            'stakes' => self::generateStakes($collision),
        ];

        return new self([
            'world_state_id' => $world->id,
            'seed_type' => 'conflict',
            'source_axes' => $collision['axes'],
            'content' => $content,
            'relevance_score' => $collision['tension'],
            'tension_level' => $collision['tension'],
            'archetype' => self::determineArchetype($collision),
        ]);
    }

    /**
     * Generate character seed from structural anchor
     */
    public static function fromStructuralAnchor(WorldState $world, string $anchor): self
    {
        $content = [
            'character' => self::generateAnchorCharacter($anchor),
            'motivation' => self::generateAnchorMotivation($anchor),
            'conflict_source' => self::generateAnchorConflict($anchor),
            'growth_arc' => self::generateGrowthArc($anchor),
        ];

        return new self([
            'world_state_id' => $world->id,
            'seed_type' => 'character',
            'source_axes' => 'structural_anchor',
            'content' => $content,
            'relevance_score' => 0.9, // High relevance to author intent
            'tension_level' => 0.6,
            'archetype' => $anchor,
        ]);
    }

    private static function generateConflict(array $collision): array
    {
        $conflicts = [
            'power_resource' => [
                'title' => 'Resource Control Struggle',
                'description' => 'Those with power seek to control scarce resources',
                'escalation' => 'Political maneuvering → Economic warfare → Open conflict'
            ],
            'power_perception' => [
                'title' => 'Ideological Power Challenge',
                'description' => 'Rising beliefs challenge established power structures',
                'escalation' => 'Debate → Persecution → Revolution'
            ],
            'resource_perception' => [
                'title' => 'Value System Crisis',
                'description' => 'New resources undermine traditional beliefs',
                'escalation' => 'Adoption → Conflict → Transformation'
            ]
        ];

        return $conflicts[$collision['axes']] ?? [
            'title' => 'System Collision',
            'description' => 'Multiple forces create complex tension',
            'escalation' => 'Discovery → Tension → Resolution'
        ];
    }

    private static function generateCharacters(array $collision): array
    {
        return [
            'protagonist' => [
                'role' => 'Caught between systems',
                'motivation' => 'Find balance or choose side',
                'growth' => 'Confused → Determined → Transformed'
            ],
            'antagonist' => [
                'role' => 'Defender of old order',
                'motivation' => 'Maintain stability/control',
                'growth' => 'Confident → Challenged → Adapted/Defeated'
            ],
            'catalyst' => [
                'role' => 'Agent of change',
                'motivation' => 'Accelerate transformation',
                'growth' => 'Mysterious → Revealed → Integrated'
            ]
        ];
    }

    private static function generateSetting(WorldState $world, array $collision): array
    {
        return [
            'location' => 'Intersection point of conflicting systems',
            'atmosphere' => 'Tense, charged with potential change',
            'sensory_details' => [
                'sight' => 'Contrasting symbols of power meeting',
                'sound' => 'Arguments, whispers, preparations',
                'feeling' => 'Electric, inevitable change'
            ]
        ];
    }

    private static function generateStakes(array $collision): array
    {
        return [
            'personal' => 'Identity, relationships, survival',
            'social' => 'Power structures, resource distribution',
            'ideological' => 'Belief systems, future direction',
            'escalation' => 'Local → Regional → World-changing'
        ];
    }

    private static function determineArchetype(array $collision): string
    {
        $archetypes = [
            'power_resource' => 'power_struggle',
            'power_perception' => 'ideological_conflict', 
            'resource_perception' => 'value_transformation'
        ];

        return $archetypes[$collision['axes']] ?? 'system_collision';
    }

    private static function generateAnchorCharacter(string $anchor): array
    {
        $characters = [
            'academic_system' => [
                'name' => 'The Scholar-Protégé',
                'role' => 'Student discovering hidden truths',
                'skills' => ['Learning', 'Research', 'Pattern recognition']
            ],
            'faction_system' => [
                'name' => 'The Faction Operative',
                'role' => 'Agent balancing loyalty and morality',
                'skills' => ['Infiltration', 'Negotiation', 'Combat']
            ],
            'commercial_system' => [
                'name' => 'The Merchant-Explorer',
                'role' => 'Trader connecting disparate systems',
                'skills' => ['Commerce', 'Navigation', 'Diplomacy']
            ]
        ];

        return $characters[$anchor] ?? ['name' => 'The System-Bridge', 'role' => 'Connector', 'skills' => ['Adaptation']];
    }

    private static function generateAnchorMotivation(string $anchor): string
    {
        $motivations = [
            'academic_system' => 'Uncover forbidden knowledge that changes everything',
            'faction_system' => 'Navigate between competing loyalties while maintaining honor',
            'commercial_system' => 'Create prosperity by bridging isolated markets'
        ];

        return $motivations[$anchor] ?? 'Find harmony between conflicting forces';
    }

    private static function generateAnchorConflict(string $anchor): string
    {
        $conflicts = [
            'academic_system' => 'Institutional rules vs dangerous truths',
            'faction_system' => 'Duty to faction vs personal morality',
            'commercial_system' => 'Profit ethics vs survival necessities'
        ];

        return $conflicts[$anchor] ?? 'System requirements vs individual needs';
    }

    private static function generateGrowthArc(string $anchor): array
    {
        return [
            'beginning' => 'Naïve acceptance of system rules',
            'middle' => 'Discovery of system contradictions and flaws',
            'end' => 'Transformation of self or system'
        ];
    }
}
