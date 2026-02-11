<?php

namespace App\Domains\World\ValueObjects;

class Claim
{
    public function __construct(
        public string $type,         // e.g., 'RESURRECTION', 'CULTIVATION_BREAKTHROUGH', 'SPELL_CAST'
        public ?int $magnitude,      // 1-10 scale
        public ?string $subject = null,
        public ?string $location = null
    ) {}
}
