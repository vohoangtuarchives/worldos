<?php

declare(strict_types=1);

namespace Tuzy\Domain\Replay\ValueObject;

/**
 * Cursor for deterministic replay (seed hash + call counter).
 * Domain-only; no Laravel.
 */
final readonly class ReplayCursor
{
    public function __construct(
        public string $seedHash,
        public int $callCounter = 0,
    ) {
    }
}
