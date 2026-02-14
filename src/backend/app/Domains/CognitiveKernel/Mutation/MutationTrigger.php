<?php

namespace App\Domains\CognitiveKernel\Mutation;

use App\Domains\CognitiveKernel\Archetype;

/**
 * Mutation Trigger
 * 
 * Represents a detected mutation trigger event
 */
class MutationTrigger
{
    public const EXTREME_COLLAPSE = 'EXTREME_COLLAPSE';
    public const MYTH_PARADOX = 'MYTH_PARADOX';
    public const REPEATED_FAILURE = 'REPEATED_FAILURE';

    public function __construct(
        public readonly string $type,
        public readonly Archetype $archetype,
        public readonly array $context = []
    ) {
        // Validate trigger type
        if (!in_array($type, [self::EXTREME_COLLAPSE, self::MYTH_PARADOX, self::REPEATED_FAILURE])) {
            throw new \InvalidArgumentException("Invalid mutation trigger type: {$type}");
        }
    }

    /**
     * Get severity of this trigger
     */
    public function getSeverity(): float
    {
        return match($this->type) {
            self::EXTREME_COLLAPSE => $this->context['collapse_severity'] ?? 1.0,
            self::MYTH_PARADOX => 0.8,
            self::REPEATED_FAILURE => min(1.0, ($this->context['failure_count'] ?? 3) / 5),
        };
    }

    /**
     * Convert to array for storage
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'archetype_key' => $this->archetype->key,
            'severity' => $this->getSeverity(),
            'context' => $this->context,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
