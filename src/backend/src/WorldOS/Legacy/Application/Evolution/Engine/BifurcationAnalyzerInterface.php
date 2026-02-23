<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Engine;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Evolution\Domain\Legacy\EvolutionContext;
use WorldOS\Evolution\Domain\Legacy\ValueObject\BranchEvent;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\VectorForce;

interface BifurcationAnalyzerInterface
{
    public function analyze(
        WorldStateVector $state,
        WorldStateVector $prevState,
        VectorForce $netForce,
        EvolutionContext $context
    ): ?BranchEvent;
}
