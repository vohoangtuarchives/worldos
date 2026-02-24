<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\ValueObjects;

/**
 * PressureSignal — narrative-generated pressure on simulation.
 *
 * From docs §16.5: Chapter → EventExtractor → PressureSignal →
 * Runtime.injectPressure() → PhaseEngine evaluation.
 *
 * Narrative creates CONDITIONS for phase transitions, not direct mutations.
 */
final readonly class PressureSignal
{
    /**
     * @param string   $source          Source identifier (e.g., "chapter_42")
     * @param float    $magnitude       Pressure strength (0-1)
     * @param string[] $targetDimensions Which state dimensions are affected
     * @param string   $description     Human-readable description
     */
    public function __construct(
        public string $source,
        public float $magnitude,
        public array $targetDimensions,
        public string $description,
    ) {
    }
}
