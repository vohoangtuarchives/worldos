<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\ValueObject;

/**
 * Signal from narrative (or other source) to inject pressure into PhaseEngine.
 * PhaseEngine may use this to drive phase transition.
 */
readonly class PressureSignal
{
    public function __construct(
        public string $universeId,
        public float $intensity,
        public string $source = 'narrative',
        public ?int $seriesId = null,
        public ?int $chapterSequence = null,
    ) {
        // Clamp intensity 0-1; readonly prevents assignment in body, so callers should pass clamped value or use factory
    }

    public static function create(string $universeId, float $intensity, string $source = 'narrative', ?int $seriesId = null, ?int $chapterSequence = null): self
    {
        return new self($universeId, max(0.0, min(1.0, $intensity)), $source, $seriesId, $chapterSequence);
    }
}
