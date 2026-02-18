<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Sensitivity;

use App\Domains\Evolution\EvolutionContext;
use App\Domains\Evolution\ValueObjects\VectorForce;

interface SensitivityMatrixInterface
{
    public function apply(VectorForce $rawForce, EvolutionContext $context): VectorForce;
}
