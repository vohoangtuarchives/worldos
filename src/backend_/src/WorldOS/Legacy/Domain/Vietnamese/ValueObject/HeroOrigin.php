<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Vietnamese\ValueObject;

/**
 * Origin descriptor for a Vietnamese-style hero (realm, dimension).
 * Domain-only; WorldOS\Saga\Domain\Hero\Entity\Hero is the aggregate.
 */
final readonly class HeroOrigin
{
    public function __construct(
        public string $realm,
        public string $dimension = 'default',
    ) {
    }
}
