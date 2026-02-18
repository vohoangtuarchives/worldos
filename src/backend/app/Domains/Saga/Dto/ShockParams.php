<?php

declare(strict_types=1);

namespace App\Domains\Saga\DTO;

/**
 * Phase 4.2: Optional shock to apply during a tick (Saga mode).
 */
final readonly class ShockParams
{
    public function __construct(
        public float $magnitude,
        public string $type,
    ) {
    }
}
