<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Seed\ValueObject;

use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;

final class UniverseSeed
{
    public function __construct(
        public readonly MythVector $mythImprint,
        public readonly array $couplingMatrix,
        public readonly float $spectralRadius,
        public readonly float $entropyResidual,
        public readonly int $generation,
        public readonly ?string $parentUniverseId,
    ) {
    }
}
