<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\CivilizationDynamics\ValueObject;

/**
 * Immutable civilization residual (scars / trauma memory).
 * Domain-only; decay produces a new instance.
 */
final readonly class CivilizationResidual
{
    /** @param array<string, float> $scars */
    public function __construct(
        public array $scars = [],
    ) {
    }

    public function getIntensity(string $type): float
    {
        return $this->scars[$type] ?? 0.0;
    }
}
