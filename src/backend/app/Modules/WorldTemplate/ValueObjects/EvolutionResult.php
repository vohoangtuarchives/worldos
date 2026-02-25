<?php

declare(strict_types=1);

namespace App\Modules\WorldTemplate\ValueObjects;

use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\StabilityMetric;
use App\Modules\Shared\ValueObjects\WorldStateVector;

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
