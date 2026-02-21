<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Engine;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObject\BranchEvent;
use App\Domains\Evolution\ValueObjects\VectorForce;

interface BifurcationAnalyzerInterface
{
    public function analyze(
        WorldStateVector $state,
        WorldStateVector $prevState,
        VectorForce $netForce,
        EvolutionContext $context
    ): ?BranchEvent;
}
