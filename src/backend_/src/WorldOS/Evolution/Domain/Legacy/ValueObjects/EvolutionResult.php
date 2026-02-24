<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObjects;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

/**
 * EvolutionResult - Output of VectorDynamicsEngine::step().
 */
final class EvolutionResult
{
    public function __construct(
        public readonly WorldStateVector $nextState,
        public readonly VectorForce $netForce,
        public readonly ?BranchEvent $branch = null
    ) {
    }
}
