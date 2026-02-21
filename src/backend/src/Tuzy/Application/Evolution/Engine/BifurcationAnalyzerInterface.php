<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Engine;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObject\BranchEvent;
use Tuzy\Domain\Evolution\ValueObjects\VectorForce;

interface BifurcationAnalyzerInterface
{
    public function analyze(
        WorldStateVector $state,
        WorldStateVector $prevState,
        VectorForce $netForce,
        EvolutionContext $context
    ): ?BranchEvent;
}
