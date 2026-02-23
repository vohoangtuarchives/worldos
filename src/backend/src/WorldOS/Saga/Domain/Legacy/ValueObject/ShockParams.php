<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Legacy\ValueObject;

/**
 * Optional shock to apply during a tick (Saga mode).
 */
readonly class ShockParams
{
    public function __construct(
        public float $magnitude,
        public string $type,
    ) {
    }
}
