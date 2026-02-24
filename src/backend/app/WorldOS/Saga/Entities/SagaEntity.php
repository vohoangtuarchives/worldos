<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Entities;

use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Saga\ValueObjects\SagaId;
use App\WorldOS\Saga\ValueObjects\SagaStatus;
use App\WorldOS\World\ValueObjects\WorldId;
use DateTimeImmutable;
use LogicException;

/**
 * Saga Entity — Aggregate Root.
 *
 * A Saga is an experiment orchestrator that manages the lifecycle
 * of one or more Universes across one or more Worlds.
 *
 * Key invariants:
 *   - Only ACTIVE sagas can advance
 *   - A Saga tracks which Worlds and Universes belong to it
 *   - Saga does NOT tick Worlds directly — it ticks Universes
 *
 * Pure PHP — NO Eloquent dependency.
 */
final class SagaEntity
{
    private SagaStatus $status;

    /**
     * @var array<array{world_id: string, universe_id: string, sequence: int}> World-Universe links
     */
    private array $worlds;

    /** @var array<object> Domain events pending dispatch */
    private array $pendingEvents = [];

    /**
     * @param array<array{world_id: string, universe_id: string, sequence: int}> $worlds
     */
    public function __construct(
        private readonly SagaId $id,
        private readonly string $name,
        private readonly ?string $presetKey,
        private readonly DateTimeImmutable $createdAt,
        ?SagaStatus $status = null,
        array $worlds = [],
    ) {
        $this->status = $status ?? SagaStatus::ACTIVE;
        $this->worlds = $worlds;
    }

    // ──────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────

    public function getId(): SagaId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPresetKey(): ?string
    {
        return $this->presetKey;
    }

    public function getStatus(): SagaStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array<array{world_id: string, universe_id: string, sequence: int}>
     */
    public function getWorlds(): array
    {
        return $this->worlds;
    }

    /**
     * @return string[] Universe IDs in this Saga
     */
    public function getUniverseIds(): array
    {
        return array_map(fn(array $w) => $w['universe_id'], $this->worlds);
    }

    // ──────────────────────────────────────────
    // Business Methods
    // ──────────────────────────────────────────

    /**
     * Add a World+Universe pair to this Saga.
     */
    public function addWorld(WorldId $worldId, UniverseId $universeId, int $sequence): void
    {
        $this->guardCanAdvance();

        $this->worlds[] = [
            'world_id' => $worldId->value,
            'universe_id' => $universeId->value,
            'sequence' => $sequence,
        ];
    }

    public function canAdvance(): bool
    {
        return $this->status->canAdvance();
    }

    public function complete(): void
    {
        if (!$this->status->canComplete()) {
            throw new LogicException(
                "Cannot complete Saga [{$this->id}] with status [{$this->status->value}]"
            );
        }

        $this->status = SagaStatus::COMPLETED;
    }

    public function archive(): void
    {
        if (!$this->status->canArchive()) {
            throw new LogicException(
                "Cannot archive Saga [{$this->id}]: already archived"
            );
        }

        $this->status = SagaStatus::ARCHIVED;
    }

    public function getNextSequence(): int
    {
        if (empty($this->worlds)) {
            return 1;
        }

        return max(array_column($this->worlds, 'sequence')) + 1;
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

    private function guardCanAdvance(): void
    {
        if (!$this->canAdvance()) {
            throw new LogicException(
                "Saga [{$this->id}] cannot advance: status is [{$this->status->value}]"
            );
        }
    }
}
