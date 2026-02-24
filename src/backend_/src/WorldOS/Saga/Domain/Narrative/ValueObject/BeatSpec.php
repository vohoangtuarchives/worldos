<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Narrative\ValueObject;

readonly class BeatSpec
{
    public function __construct(
        public string $emotion,
        public float $tension,
        public string $arcContext,
        public array $primaryCharacters = [],
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
