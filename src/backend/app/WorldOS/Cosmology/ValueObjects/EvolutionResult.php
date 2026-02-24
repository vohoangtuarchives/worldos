<?php

declare(strict_types=1);

namespace App\WorldOS\Cosmology\ValueObjects;

use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\StabilityMetric;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Evolution Result — output of WorldEvolutionKernel after one tick.
 *
 * Contains the new state vectors, stability, and any phase transitions detected.
 */
final readonly class EvolutionResult
{
    /**
     * @param PhaseTransition[] $phaseTransitions
     * @param array<string, mixed> $metrics
     */
    public function __construct(
        public WorldStateVector $newStateVector,
        public CascadeStateVector $newCascadeState,
        public StabilityMetric $stability,
        public array $phaseTransitions = [],
        public bool $collapseDetected = false,
        public ?string $collapseReason = null,
        public array $metrics = [],
    ) {
    }

    public function hasPhaseTransitions(): bool
    {
        return !empty($this->phaseTransitions);
    }
}
