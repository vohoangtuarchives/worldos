<?php

namespace App\Domains\WriterConsole;

class ValidationResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly ?string $reason = null
    ) {}

    public static function allow(): self
    {
        return new self(true);
    }

    public static function deny(string $reason): self
    {
        return new self(false, $reason);
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}
