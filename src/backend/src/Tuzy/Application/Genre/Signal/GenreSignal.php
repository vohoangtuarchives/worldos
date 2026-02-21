<?php

namespace Tuzy\Application\Genre\Signal;

class GenreSignal
{
    /**
     * @param array<string, float> $impacts Key: genre_key, Value: impact (negative or positive)
     */
    public function __construct(
        public readonly string $sourceId,
        public readonly array $impacts,
        public readonly float $permanence = 1.0 // 1.0 = permanent, < 1.0 = decays
    ) {}
}
