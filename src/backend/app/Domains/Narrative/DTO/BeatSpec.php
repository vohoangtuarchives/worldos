<?php

declare(strict_types=1);

namespace App\Domains\Narrative\DTO;

/**
 * Structured output from BeatPlanner: emotion, tension, arc context, optional characters and world signals.
 */
final readonly class BeatSpec
{
    public function __construct(
        public string $emotion,
        public float $tension,
        public string $arcContext,
        /** @var list<string> */
        public array $primaryCharacters = [],
        /** @var array<string, mixed> */
        public array $worldSignals = [],
    ) {
    }

    public function toBlueprintFragment(): array
    {
        return [
            'emotional_objective' => $this->emotion,
            'arc_context' => $this->arcContext,
            'tension' => $this->tension,
        ];
    }
}
