<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Sensitivity;

use WorldOS\Evolution\Domain\Legacy\EvolutionContext;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\VectorForce;

interface SensitivityMatrixInterface
{
    public function apply(VectorForce $rawForce, EvolutionContext $context): VectorForce;
}
