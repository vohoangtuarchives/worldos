<?php

namespace App\Domains\Genre\Validation\Violations;

class ForbiddenConcept
{
    public function __construct(
        public string $reason
    ) {}
}
