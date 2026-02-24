<?php

namespace WorldOS\Blueprint\Domain\Legacy\Aggregates;

use WorldOS\Blueprint\Domain\Legacy\ValueObject\EntropyScore;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\GeneVector;
use Illuminate\Support\Collection;
use DateTime;

class WorldAggregate
{
    public static function create(
        string $name,
        string $preset,
        GeneVector $geneVector,
        EntropyScore $entropy,
        int $tick = 0,
        bool $autonomous = false
    ): self {
        $id = 'world_' . bin2hex(random_bytes(8));
        $aggregate = new self(
            $id,
            $name,
            $preset,
            $geneVector,
            $entropy,
            $tick,
            $autonomous,
            null,
            new DateTime(),
            new DateTime()
        );

        // Dispatch Event (Tuzy domain event via Laravel bus)
        event(new \WorldOS\Blueprint\Domain\Legacy\Event\WorldDefined($id, $name));

        return $aggregate;
    }

    public function __construct(
        private string $id,
        private string $name,
        private string $preset,
        private GeneVector $geneVector,
        private EntropyScore $entropyScore,
        private int $currentTick,
        private bool $autonomous,
        private ?DateTime $lastTickAt,
        private DateTime $createdAt,
        private DateTime $updatedAt
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function preset(): string
    {
        return $this->preset;
    }

    public function geneVector(): GeneVector
    {
        return $this->geneVector;
    }

    public function currentEntropy(): EntropyScore
    {
        return $this->entropyScore;
    }

    public function currentTick(): int
    {
        return $this->currentTick;
    }

    public function isAutonomous(): bool
    {
        return $this->autonomous;
    }

    public function lastTickAt(): ?DateTime
    {
        return $this->lastTickAt;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function advanceTick(int $tick, EntropyScore $entropyScore, Collection $shockEvents): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->preset,
            $this->geneVector,
            $entropyScore,
            $tick,
            $this->autonomous,
            new DateTime(),
            $this->createdAt,
            $this->updatedAt
        );
    }

    public function factionInstability(): float
    {
        return 0.1;
    }

    public function resourceScarcity(): float
    {
        return 0.1;
    }

    public function mythInstability(): float
    {
        return 0.0;
    }

    public function getRegions(): array
    {
        return ['world_center'];
    }

    public function getRegionInstability(string $region): float
    {
        return 0.1;
    }
}
