<?php

namespace App\Domains\Genre\Validation;

class ValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $violations = [],
        public readonly bool $repairable = false
    ) {}
}
