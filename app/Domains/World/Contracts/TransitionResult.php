<?php

namespace App\Domains\World\Contracts;

class TransitionResult
{
    public function __construct(
        public bool $allowed,
        public array $warnings = [],
        public array $requiredMigrations = []
    ) {}
}
