<?php

namespace WorldOS\Legacy\Domain\CognitiveKernel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ArchetypeMutation extends Model
{
    use HasUuids;

    protected $fillable = [
        'parent_archetype',
        'variant_1',
        'variant_2',
        'trigger_type',
        'trigger_context',
        'origin_world_id',
        'origin_saga_id',
        'irreversible'
    ];

    protected $casts = [
        'trigger_context' => 'array',
        'irreversible' => 'boolean',
    ];

    /**
     * Mutation trigger types
     */
    public const TRIGGER_EXTREME_COLLAPSE = 'EXTREME_COLLAPSE';
    public const TRIGGER_MYTH_PARADOX = 'MYTH_PARADOX';
    public const TRIGGER_REPEATED_FAILURE = 'REPEATED_FAILURE';

    /**
     * Get the parent archetype
     */
    public function parentArchetype(): ?Archetype
    {
        return Archetype::findByKey($this->parent_archetype);
    }

    /**
     * Get variant archetypes (if they've been created)
     */
    public function variants(): array
    {
        return [
            Archetype::findByKey($this->variant_1),
            Archetype::findByKey($this->variant_2),
        ];
    }
}
