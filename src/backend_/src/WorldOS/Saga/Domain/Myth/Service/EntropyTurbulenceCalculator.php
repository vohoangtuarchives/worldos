<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Myth\Service;

use WorldOS\Saga\Domain\Saga\ValueObject\UniverseMacroMetrics;

final class EntropyTurbulenceCalculator
{
    public function compute(UniverseMacroMetrics $m): float
    {
        return $m->entropyVariance + abs($m->entropyGradient) + $m->tensionOscillation;
    }
}
