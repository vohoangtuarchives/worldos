<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Material\ValueObject;

/**
 * Record of a change to a material instance (audit / history).
 */
final readonly class MaterialChange
{
    public function __construct(
        private string $materialInstanceId,
        private string $changeType,
        private array $oldValue,
        private array $newValue,
        private ?string $reason,
        private ?int $epoch,
        private \DateTimeInterface $createdAt,
    ) {
    }

    public function instanceId(): string
    {
        return $this->materialInstanceId;
    }

    public function type(): string
    {
        return $this->changeType;
    }

    /** @return array<string, mixed> */
    public function oldValue(): array
    {
        return $this->oldValue;
    }

    /** @return array<string, mixed> */
    public function newValue(): array
    {
        return $this->newValue;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function epoch(): ?int
    {
        return $this->epoch;
    }

    public function createdAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
