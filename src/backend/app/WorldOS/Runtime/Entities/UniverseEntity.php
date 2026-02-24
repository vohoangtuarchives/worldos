<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Entities;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Runtime\ValueObjects\UniverseStatus;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\Seed;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use App\WorldOS\World\ValueObjects\WorldId;
use DateTimeImmutable;
use LogicException;

/**
 * Universe Entity — Aggregate Root.
 *
 * A Universe is a runtime instance (phenotype) of a World (genotype).
 * It has its own evolving state, history, and potential for forking.
 *
 * Key invariants:
 *   - Only RUNNING universes can tick
 *   - Tick always increments by 1
 *   - State mutations produce new snapshots (handled externally)
 *   - Collapsed/Archived universes are terminal states
 *
 * Pure PHP — NO Eloquent dependency.
 */
final class UniverseEntity
{
    private WorldStateVector $stateVector;
    private CascadeStateVector $cascadeState;
    private int $currentTick;
    private int $age;
    private UniverseStatus $status;

    /** @var array<string, mixed> */
    private array $parameters;

    /** @var array<object> Domain events pending dispatch */
    private array $pendingEvents = [];

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly UniverseId $id,
        private readonly WorldId $worldId,
        WorldStateVector $stateVector,
        CascadeStateVector $cascadeState,
        private readonly Seed $seed,
        private readonly ?UniverseId $parentUniverseId,
        private readonly DateTimeImmutable $createdAt,
        int $currentTick = 0,
        int $age = 0,
        ?UniverseStatus $status = null,
        array $parameters = [],
    ) {
        $this->stateVector = $stateVector;
        $this->cascadeState = $cascadeState;
        $this->currentTick = $currentTick;
        $this->age = $age;
        $this->status = $status ?? UniverseStatus::RUNNING;
        $this->parameters = $parameters;
    }

    // ──────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────

    public function getId(): UniverseId
    {
        return $this->id;
    }

    public function getWorldId(): WorldId
    {
        return $this->worldId;
    }

    public function getStateVector(): WorldStateVector
    {
        return $this->stateVector;
    }

    public function getCascadeState(): CascadeStateVector
    {
        return $this->cascadeState;
    }

    public function getCurrentTick(): int
    {
        return $this->currentTick;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getStatus(): UniverseStatus
    {
        return $this->status;
    }

    public function getSeed(): Seed
    {
        return $this->seed;
    }

    public function getParentUniverseId(): ?UniverseId
    {
        return $this->parentUniverseId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    // ──────────────────────────────────────────
    // Business Methods
    // ──────────────────────────────────────────

    /**
     * Apply evolution results from WorldEvolutionKernel.
     * Only callable when RUNNING.
     */
    public function applyEvolution(
        WorldStateVector $newState,
        CascadeStateVector $newCascade,
    ): void {
        $this->guardCanTick();
        $this->stateVector = $newState;
        $this->cascadeState = $newCascade;
    }

    /**
     * Increment the tick counter by 1. Called after evolution is applied.
     */
    public function incrementTick(): void
    {
        $this->guardCanTick();
        $this->currentTick++;
        $this->age++;
    }

    /**
     * Check if this Universe can receive another tick.
     */
    public function canTick(): bool
    {
        return $this->status->canTick();
    }

    /**
     * Pause this Universe — temporarily suspends ticking.
     */
    public function pause(): void
    {
        if (!$this->status->canPause()) {
            throw new LogicException(
                "Cannot pause Universe [{$this->id}] with status [{$this->status->value}]"
            );
        }

        $this->status = UniverseStatus::PAUSED;
    }

    /**
     * Resume a paused Universe.
     */
    public function resume(): void
    {
        if (!$this->status->canResume()) {
            throw new LogicException(
                "Cannot resume Universe [{$this->id}] with status [{$this->status->value}]"
            );
        }

        $this->status = UniverseStatus::RUNNING;
    }

    /**
     * Mark Universe as collapsed — terminal state.
     */
    public function collapse(string $cause): void
    {
        if (!$this->status->canCollapse()) {
            throw new LogicException(
                "Cannot collapse Universe [{$this->id}]: already terminal"
            );
        }

        $this->status = UniverseStatus::COLLAPSED;
    }

    /**
     * Archive Universe — terminal state for permanent storage.
     */
    public function archive(): void
    {
        if (!$this->status->canArchive()) {
            throw new LogicException(
                "Cannot archive Universe [{$this->id}]: already archived"
            );
        }

        $this->status = UniverseStatus::ARCHIVED;
    }

    /**
     * Check if this Universe is a fork (has parent).
     */
    public function isFork(): bool
    {
        return $this->parentUniverseId !== null;
    }

    /**
     * Check if Universe has reached a terminal state.
     */
    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    /**
     * Derive the seeded RNG value for the current tick.
     */
    public function currentTickSeed(): Seed
    {
        return $this->seed->deriveForTick($this->currentTick);
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

    private function guardCanTick(): void
    {
        if (!$this->canTick()) {
            throw new LogicException(
                "Universe [{$this->id}] cannot tick: status is [{$this->status->value}]"
            );
        }
    }
}
