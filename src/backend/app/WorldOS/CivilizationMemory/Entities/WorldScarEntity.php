<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Entities;

use App\WorldOS\CivilizationMemory\ValueObjects\ScarId;
use App\WorldOS\CivilizationMemory\ValueObjects\ScarWeight;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use DateTimeImmutable;
use LogicException;

/**
 * World Scar Entity — IMMUTABLE permanent consequence of history.
 *
 * From docs §8.4 & Foundation Rules §2.2:
 *   "Scars are the permanent sediment of history. They cannot be erased."
 *   "History cannot be rewritten. Consequences are permanent."
 *
 * Scars add inertia to the simulation: a great war 2000 years ago
 * leaves war_trauma that decays slowly but never fully disappears.
 *
 * IMMUTABLE after creation — updating/deleting throws LogicException.
 *
 * Pure PHP — NO Eloquent dependency.
 */
final class WorldScarEntity
{
    /** @var array<object> Domain events pending dispatch */
    private array $pendingEvents = [];

    public function __construct(
        private readonly ScarId $id,
        private readonly UniverseId $universeId,
        private readonly string $sourceEvent,
        private readonly string $type,
        private readonly ScarWeight $weight,
        private readonly string $description,
        private readonly int $tickCreated,
        private readonly DateTimeImmutable $createdAt,
        private float $currentIntensity = -1.0,
    ) {
        if ($this->currentIntensity < 0) {
            $this->currentIntensity = $this->weight->value / 10.0;
        }
    }

    // ──────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────

    public function getId(): ScarId
    {
        return $this->id;
    }

    public function getUniverseId(): UniverseId
    {
        return $this->universeId;
    }

    public function getSourceEvent(): string
    {
        return $this->sourceEvent;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getWeight(): ScarWeight
    {
        return $this->weight;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTickCreated(): int
    {
        return $this->tickCreated;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCurrentIntensity(): float
    {
        return $this->currentIntensity;
    }

    // ──────────────────────────────────────────
    // Business Methods
    // ──────────────────────────────────────────

    /**
     * Calculate the decay of this scar over elapsed ticks.
     * Scars fade but NEVER fully disappear.
     *
     * Decay formula: intensity = baseIntensity × e^(-λt)
     * where λ = 0.001 (very slow decay), t = elapsed ticks
     * Minimum intensity = 0.01 (scars are permanent).
     */
    public function calculateDecayedIntensity(int $currentTick): float
    {
        $elapsed = max(0, $currentTick - $this->tickCreated);
        $baseIntensity = $this->weight->value / 10.0;
        $lambda = 0.001; // Very slow decay rate

        $decayed = $baseIntensity * exp(-$lambda * $elapsed);

        // Scars never fully disappear
        return max(0.01, $decayed);
    }

    /**
     * Update the cached current intensity based on tick.
     */
    public function updateIntensityForTick(int $currentTick): void
    {
        $this->currentIntensity = $this->calculateDecayedIntensity($currentTick);
    }

    /**
     * Calculate the pressure this scar exerts on the state vector.
     * Heavy scars with recent creation exert more pressure.
     */
    public function calculatePressure(int $currentTick): float
    {
        return $this->calculateDecayedIntensity($currentTick) * ($this->weight->value / 10.0);
    }

    // ──────────────────────────────────────────
    // IMMUTABILITY GUARDS
    // ──────────────────────────────────────────

    /**
     * Scars are IMMUTABLE — this guard prevents any attempt to modify core properties.
     *
     * @throws LogicException always
     */
    public static function guardImmutable(): never
    {
        throw new LogicException(
            'WorldScar is IMMUTABLE. History cannot be rewritten. Consequences are permanent.'
        );
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
}
