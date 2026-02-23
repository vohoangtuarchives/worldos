<?php

namespace WorldOS\Legacy\Domain\Genre\Contracts;

interface GenreSignalDefinition
{
    public function key(): string;
    
    /**
     * Define words and their associated genre weights.
     * Used by VocabularyResolver to calculate GenreProfile.
     */
    public function vocabularySignals(): array;

    /**
     * Define events and their associated signal impact.
     */
    public function eventSignals(): array;

    /**
     * Thresholds for enforcing consistency at certain power stages.
     */
    public function consistencyRules(float $dominance): array;
}
