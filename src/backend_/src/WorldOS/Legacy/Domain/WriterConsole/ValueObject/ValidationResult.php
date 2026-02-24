<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\WriterConsole\ValueObject;

final readonly class ValidationResult
{
    public function __construct(
        public bool $allowed,
        public ?string $reason = null,
    ) {
    }

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
