<?php

declare(strict_types=1);

namespace WorldOS\Society\Institution\Entity;

/**
 * Domain entity: Institution (plain PHP, no Eloquent).
 */
final class Institution
{
    public function __construct(
        private string $id,
        private string $worldId,
        private array $charterValues = [],
        private float $publicTrust = 0.0,
        private float $authorityLevel = 0.0,
        private int $createdTick = 0,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWorldId(): string
    {
        return $this->worldId;
    }

    /** @return array<string, mixed> */
    public function getCharterValues(): array
    {
        return $this->charterValues;
    }

    public function getPublicTrust(): float
    {
        return $this->publicTrust;
    }

    public function getAuthorityLevel(): float
    {
        return $this->authorityLevel;
    }

    public function getCreatedTick(): int
    {
        return $this->createdTick;
    }
}
