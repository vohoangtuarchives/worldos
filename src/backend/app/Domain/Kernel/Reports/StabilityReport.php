<?php

declare(strict_types=1);

namespace App\Domain\Kernel\Reports;

/**
 * Immutable object encapsulating stability results required for Experiment Logs.
 */
final class StabilityReport
{
    public function __construct(
        public readonly float $spectralRadius,
        public readonly float $margin,
        public readonly float $maxGershgorinBound,
        public readonly array $gershgorinViolations,
        public readonly bool $isContractive
    ) {}
}
