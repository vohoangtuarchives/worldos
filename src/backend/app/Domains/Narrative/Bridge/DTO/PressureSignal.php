<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Bridge\DTO;

/**
 * WorldOS 2.0 Clean: Signal from narrative (or other source) to inject pressure into PhaseEngine
 * instead of mutating Universe vector directly. PhaseEngine may use this to drive phase transition.
 */
final readonly class PressureSignal
{
    public string $universeId;
    public float $intensity;
    public string $source;
    public ?int $seriesId;
    public ?int $chapterSequence;

    public function __construct(
        string $universeId,
        float $intensity,
        string $source = 'narrative',
        ?int $seriesId = null,
        ?int $chapterSequence = null,
    ) {
        $this->universeId = $universeId;
        $this->intensity = max(0.0, min(1.0, $intensity));
        $this->source = $source;
        $this->seriesId = $seriesId;
        $this->chapterSequence = $chapterSequence;
    }
}
