<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Myth\Entity;

use WorldOS\Saga\Domain\Myth\ValueObject\MythRegime;

final class MythRegimeState
{
    public function __construct(
        public readonly MythRegime $current,
        public readonly float $inertia,
        public readonly float $volatility,
        public readonly float $basinDepth,
    ) {
    }
}
