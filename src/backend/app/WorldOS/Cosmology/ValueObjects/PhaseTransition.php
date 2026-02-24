<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\ValueObjects;

/**
 * Phase Transition — records a detected transition between simulation phases.
 */
final readonly class PhaseTransition
{
    public function __construct(
        public string $type,       // e.g. 'cascade_activation', 'collapse', 'bifurcation'
        public string $from,       // Previous phase/state label
        public string $to,         // New phase/state label
        public int $tick,          // When it occurred
        public float $magnitude,   // Strength of transition [0,1]
        public string $description,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'from' => $this->from,
            'to' => $this->to,
            'tick' => $this->tick,
            'magnitude' => $this->magnitude,
            'description' => $this->description,
        ];
    }
}
