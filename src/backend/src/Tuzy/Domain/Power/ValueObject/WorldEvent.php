<?php

declare(strict_types=1);

namespace Tuzy\Domain\Power\ValueObject;

final readonly class WorldEvent
{
    public function __construct(
        public string $id,
        public string $type,
        public float $magnitude,
        public float $permanence,
        public string $visibility,
        public int $epoch,
    ) {
    }
}
