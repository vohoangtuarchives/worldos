<?php

declare(strict_types=1);

namespace Tuzy\Domain\Vietnamese\ValueObject;

/**
 * Origin descriptor for a Vietnamese-style hero (realm, dimension).
 * Domain-only; Tuzy\Domain\Heroes\Entity\WorldHero is the aggregate.
 */
final readonly class HeroOrigin
{
    public function __construct(
        public string $realm,
        public string $dimension = 'default',
    ) {
    }
}
