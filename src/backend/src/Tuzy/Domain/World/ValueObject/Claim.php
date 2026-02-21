<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\ValueObject;

class Claim
{
    public function __construct(
        public readonly string $type,
        public readonly ?int $magnitude,
        public readonly ?string $subject = null,
        public readonly ?string $location = null,
    ) {
    }
}
