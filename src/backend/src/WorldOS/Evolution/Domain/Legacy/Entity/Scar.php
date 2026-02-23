<?php

namespace WorldOS\Evolution\Domain\Legacy\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\Entity;

class Scar
{
    private string $id;
    private string $worldId;
    private string $type; // e.g., 'war_trauma', 'metaphysical_collapse'
    private float $magnitude; // 0.0 to 1.0
    private \DateTimeImmutable $inflictedAt;

    public function __construct(string $id, string $worldId, string $type, float $magnitude, \DateTimeImmutable $inflictedAt)
    {
        $this->id = $id;
        $this->worldId = $worldId;
        $this->type = $type;
        $this->magnitude = $magnitude;
        $this->inflictedAt = $inflictedAt;
    }

    public function decay(float $amount): void
    {
        $this->magnitude -= $amount;
        if ($this->magnitude < 0.0) {
            $this->magnitude = 0.0;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMagnitude(): float
    {
        return $this->magnitude;
    }
}

