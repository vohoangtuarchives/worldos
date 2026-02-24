<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Sensitivity;

use WorldOS\Evolution\Domain\Legacy\EvolutionContext;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\VectorForce;

/**
 * IdentitySensitivityMatrix - No scaling; effectiveForce = rawForce.
 */
final class IdentitySensitivityMatrix implements SensitivityMatrixInterface
{
    public function apply(VectorForce $rawForce, EvolutionContext $context): VectorForce
    {
        return $rawForce;
    }
}
