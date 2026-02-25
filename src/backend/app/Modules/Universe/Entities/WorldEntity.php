<?php

declare(strict_types=1);

namespace App\Modules\Universe\Entities;

use App\Modules\Shared\ValueObjects\LawVector;
use App\Modules\Universe\Exceptions\WorldHaltedException;
use App\Modules\Universe\ValueObjects\WorldId;
use App\Modules\Universe\ValueObjects\WorldStatus;
use App\Modules\WorldTemplate\ValueObjects\CascadeThresholds;
use DateTimeImmutable;

/**
 * World Entity — Aggregate Root.
 *
 * A World is an immutable blueprint (genotype) defining the laws of physics
 * and archetypes for universe generation. Once defined, its LawVector
 * should not change (except via controlled DLM in future phases).
 *
 * Pure PHP — NO Eloquent dependency.
 */
final class WorldEntity
{
    private WorldStatus $status;

    /** @var array<object> Domain events pending dispatch */
    private array $pendingEvents = [];

    public function __construct(
        private readonly WorldId $id,
        private readonly string $name,
        private readonly LawVector $lawVector,
        private readonly string $presetKey,
        private readonly ?string $originType,
        private readonly DateTimeImmutable $createdAt,
        ?WorldStatus $status = null,
        private readonly ?array $config = null,
    ) {
        $this->status = $status ?? WorldStatus::ACTIVE;
    }

    /**
     * Create a World from a preset configuration.
     *
     * @param string $presetKey  Preset key (e.g. 'xianxia', 'cyberpunk')
     * @param array  $presetData Preset data from config/worldos.php
     */
    public static function createFromPreset(string $presetKey, array $presetData): self
    {
        return new self(
            id: WorldId::generate(),
            name: $presetData['name'] ?? ucfirst($presetKey),
            lawVector: LawVector::fromArray($presetData['law_vector']),
            presetKey: $presetKey,
            originType: 'preset',
            createdAt: new DateTimeImmutable(),
        );
    }

    // ──────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────

    public function getId(): WorldId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLawVector(): LawVector
    {
        return $this->lawVector;
    }

    public function getCascadeThresholds(): ?CascadeThresholds
    {
        // Currently not stored in WorldModel, returning defaults
        return CascadeThresholds::defaults();
    }

    public function generateSeed(): Seedstring
    {
        return Seedstring::from($this->presetKey);
    }

    public function getPresetKey(): string
    {
        return $this->presetKey;
    }

    public function getOriginType(): ?string
    {
        return $this->originType;
    }

    public function getStatus(): WorldStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    // ──────────────────────────────────────────
    // Business Methods
    // ──────────────────────────────────────────

    /**
     * Return a copy of this World with an overridden LawVector.
     *
     * Used by Style overlay: genre modifies physics for a tick
     * without permanently changing the World blueprint.
     */
    public function withOverriddenLaw(LawVector $overriddenLaw): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            lawVector: $overriddenLaw,
            presetKey: $this->presetKey,
            originType: $this->originType,
            createdAt: $this->createdAt,
            status: $this->status,
        );
    }

    /**
     * Check if this World can spawn a new Universe.
     */
    public function canSpawnUniverse(): bool
    {
        return $this->status->canSpawnUniverse();
    }

    /**
     * Temporarily halt this World — no new Universes can be spawned.
     *
     * @throws WorldHaltedException if already halted or dead
     */
    public function halt(): void
    {
        if (!$this->status->canHalt()) {
            throw new WorldHaltedException(
                "Cannot halt World [{$this->id}] with status [{$this->status->value}]"
            );
        }

        $this->status = WorldStatus::HALTED;
    }

    /**
     * Resume a halted World.
     *
     * @throws WorldHaltedException if not in HALTED state
     */
    public function resume(): void
    {
        if (!$this->status->canResume()) {
            throw new WorldHaltedException(
                "Cannot resume World [{$this->id}] with status [{$this->status->value}]"
            );
        }

        $this->status = WorldStatus::ACTIVE;
    }

    /**
     * Permanently terminate this World.
     *
     * @throws WorldHaltedException if already dead
     */
    public function kill(string $justification): void
    {
        if (!$this->status->canKill()) {
            throw new WorldHaltedException(
                "Cannot kill World [{$this->id}]: already DEAD"
            );
        }

        $this->status = WorldStatus::DEAD;
    }

    /**
     * Check if World is in a halted state.
     */
    public function isHalted(): bool
    {
        return $this->status === WorldStatus::HALTED;
    }

    /**
     * Check if World is permanently dead.
     */
    public function isDead(): bool
    {
        return $this->status === WorldStatus::DEAD;
    }

    // ──────────────────────────────────────────
    // Domain Events
    // ──────────────────────────────────────────

    /**
     * Record a domain event for later dispatch.
     */
    public function recordEvent(object $event): void
    {
        $this->pendingEvents[] = $event;
    }

    /**
     * Get and clear pending domain events.
     *
     * @return array<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->pendingEvents;
        $this->pendingEvents = [];

        return $events;
    }
}
