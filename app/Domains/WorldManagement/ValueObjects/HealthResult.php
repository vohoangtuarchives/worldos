<?php

namespace App\Domains\WorldManagement\ValueObjects;

use App\Domains\World\Enums\WorldHealthStatus;

class HealthResult
{
    public function __construct(
        public readonly WorldHealthStatus $status,
        public readonly array $violations = [] // Array of ['code' => string, 'message' => string]
    ) {}

    public static function stable(): self
    {
        return new self(WorldHealthStatus::STABLE);
    }
}
