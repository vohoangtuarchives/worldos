<?php

namespace App\Domains\Genre\Validation\Violations;

class ImpossibleEvent
{
    public function __construct(
        public string $reason
    ) {}
}
