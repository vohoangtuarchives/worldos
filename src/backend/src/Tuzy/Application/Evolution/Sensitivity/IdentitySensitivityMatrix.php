<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Sensitivity;

use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObjects\VectorForce;

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
