<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\Event;

final class HeroCreated
{
    public function __construct(
        public readonly string $heroId,
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
