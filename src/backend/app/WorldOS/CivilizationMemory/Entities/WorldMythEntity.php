<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Entities;

use App\WorldOS\CivilizationMemory\ValueObjects\MythId;
use App\WorldOS\CivilizationMemory\ValueObjects\MythStrength;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use DateTimeImmutable;
use LogicException;

/**
 * World Myth Entity — emergent crystallized belief pattern.
 *
 * From docs §8.4:
 *   "Myth = Crystallized belief pattern that reached critical mass."
 *   States: ACTIVE → DECAYING → MERGED
 *   SEMI-MUTABLE — can decay/merge; cannot delete, cannot boost manually.
 *
 * A Myth forms when repeated beliefs cross the MythScore threshold (≥ 0.7).
 * Myths influence the civilizational trajectory and generate narrative seeds.
 *
 * Pure PHP — NO Eloquent dependency.
 */
final class WorldMythEntity
{
    private string $state;
    private MythStrength $strength;

    /** @var array<object> */
    private array $pendingEvents = [];

    /**
     * @param string[] $beliefSources  IDs of beliefs that formed this myth
     */
    public function __construct(
        private readonly MythId $id,
        private readonly UniverseId $universeId,
        private readonly string $theme,
        private readonly string $description,
        MythStrength $strength,
        private readonly int $tickEmerged,
        private readonly DateTimeImmutable $createdAt,
        ?string $state = null,
        private readonly array $beliefSources = [],
    ) {
        $this->strength = $strength;
        $this->state = $state ?? 'active';
    }

    // ──────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────

    public function getId(): MythId
    {
        return $this->id;
    }

    public function getUniverseId(): UniverseId
    {
        return $this->universeId;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStrength(): MythStrength
    {
        return $this->strength;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getTickEmerged(): int
    {
        return $this->tickEmerged;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return string[]
     */
    public function getBeliefSources(): array
    {
        return $this->beliefSources;
    }

    public function getLevel(): int
    {
        return $this->strength->getLevel();
    }

    // ──────────────────────────────────────────
    // Business Methods
    // ──────────────────────────────────────────

    /**
     * Apply natural decay to myth strength.
     * Myths weaken when belief diverges or is challenged.
     */
    public function applyDecay(float $rate): void
    {
        $this->guardNotMerged();

        $this->strength = $this->strength->decay($rate);

        if ($this->strength->getLevel() === 0) {
            $this->state = 'decaying';
        }
    }

    /**
     * Grow myth strength from shared belief reinforcement.
     * Cannot skip levels — capped at next boundary.
     */
    public function reinforce(float $amount): void
    {
        $this->guardNotMerged();
        $this->guardActive();

        $this->strength = $this->strength->grow($amount);
    }

    /**
     * Merge this myth with another (makes this MERGED — terminal).
     * The consuming myth gains the strength.
     */
    public function merge(): void
    {
        if ($this->state === 'merged') {
            throw new LogicException(
                "Myth [{$this->id}] is already merged"
            );
        }

        $this->state = 'merged';
    }

    public function isActive(): bool
    {
        return $this->state === 'active';
    }

    public function isDecaying(): bool
    {
        return $this->state === 'decaying';
    }

    public function isMerged(): bool
    {
        return $this->state === 'merged';
    }

    /**
     * Calculate the narrative influence this myth exerts.
     * Stronger myths create stronger narrative seeds.
     */
    public function calculateInfluence(): float
    {
        if ($this->isMerged()) {
            return 0.0;
        }

        return $this->strength->value * ($this->isActive() ? 1.0 : 0.5);
    }

    // ──────────────────────────────────────────
    // Domain Events
    // ──────────────────────────────────────────

    public function recordEvent(object $event): void
    {
        $this->pendingEvents[] = $event;
    }

    /**
     * @return array<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->pendingEvents;
        $this->pendingEvents = [];

        return $events;
    }

    // ──────────────────────────────────────────
    // Guards
    // ──────────────────────────────────────────

    private function guardNotMerged(): void
    {
        if ($this->isMerged()) {
            throw new LogicException(
                "Myth [{$this->id}] is merged — cannot modify"
            );
        }
    }

    private function guardActive(): void
    {
        if (!$this->isActive()) {
            throw new LogicException(
                "Myth [{$this->id}] is not active — cannot reinforce"
            );
        }
    }
}
