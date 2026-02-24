<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\Entities;

use App\WorldOS\Attractor\ValueObjects\AttractorId;
use App\WorldOS\Attractor\ValueObjects\AttractorStatus;
use App\WorldOS\Attractor\ValueObjects\AttractorType;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use DateTimeImmutable;
use LogicException;

/**
 * Attractor Entity — Narrative Gravity Well.
 *
 * An Attractor represents a civilizational basin that pulls the
 * Universe's state vector toward a specific trajectory (e.g.,
 * Technological Singularity, Feudal Stagnation).
 *
 * From RFC-DCE §18.1: Attractor layer sits between Physics and Memory.
 * Bifurcation occurs when stability σ ≈ 0 (meta-stable).
 *
 * Lifecycle: DORMANT → ACTIVE → CAPTURED/ESCAPED
 *
 * Pure PHP — NO Eloquent dependency.
 */
final class AttractorEntity
{
    private AttractorStatus $status;
    private float $currentPull;

    /** @var array<object> Domain events pending dispatch */
    private array $pendingEvents = [];

    public function __construct(
        private readonly AttractorId $id,
        private readonly UniverseId $universeId,
        private readonly AttractorType $type,
        private readonly float $magnitude,
        private readonly float $basinDepth,
        private readonly float $activationThreshold,
        private readonly DateTimeImmutable $createdAt,
        ?AttractorStatus $status = null,
        float $currentPull = 0.0,
    ) {
        $this->status = $status ?? AttractorStatus::DORMANT;
        $this->currentPull = $currentPull;
    }

    // ──────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────

    public function getId(): AttractorId
    {
        return $this->id;
    }

    public function getUniverseId(): UniverseId
    {
        return $this->universeId;
    }

    public function getType(): AttractorType
    {
        return $this->type;
    }

    public function getMagnitude(): float
    {
        return $this->magnitude;
    }

    public function getBasinDepth(): float
    {
        return $this->basinDepth;
    }

    public function getActivationThreshold(): float
    {
        return $this->activationThreshold;
    }

    public function getStatus(): AttractorStatus
    {
        return $this->status;
    }

    public function getCurrentPull(): float
    {
        return $this->currentPull;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    // ──────────────────────────────────────────
    // Business Methods
    // ──────────────────────────────────────────

    /**
     * Calculate proximity of state vector to this attractor's basin.
     * Returns 0.0 (far) to 1.0 (inside basin).
     */
    public function calculateProximity(WorldStateVector $state): float
    {
        $conditions = $this->type->basinConditions();
        $totalDimensions = count($conditions);

        if ($totalDimensions === 0) {
            return 0.0;
        }

        $matchScore = 0.0;

        foreach ($conditions as $dimension => $range) {
            $value = $this->extractDimension($state, $dimension);

            if ($value >= $range['min'] && $value <= $range['max']) {
                $matchScore += 1.0;
            } else {
                // Partial match: distance from range
                $distance = max($range['min'] - $value, $value - $range['max'], 0.0);
                $matchScore += max(0.0, 1.0 - $distance * 2);
            }
        }

        return $matchScore / $totalDimensions;
    }

    /**
     * Update pull strength based on current state proximity.
     */
    public function updatePull(WorldStateVector $state): void
    {
        $proximity = $this->calculateProximity($state);
        $this->currentPull = $proximity * $this->magnitude;

        // Auto-activate if dormant and proximity exceeds threshold
        if ($this->status === AttractorStatus::DORMANT && $proximity >= $this->activationThreshold) {
            $this->activate();
        }

        // Capture if active and deeply inside basin
        if ($this->status === AttractorStatus::ACTIVE && $proximity >= $this->basinDepth) {
            $this->capture();
        }

        // Escape if captured but proximity drops significantly
        if ($this->status === AttractorStatus::CAPTURED && $proximity < $this->activationThreshold * 0.5) {
            $this->escape();
        }
    }

    public function activate(): void
    {
        if (!$this->status->canActivate()) {
            throw new LogicException(
                "Cannot activate Attractor [{$this->id}]: status [{$this->status->value}]"
            );
        }

        $this->status = AttractorStatus::ACTIVE;
    }

    public function capture(): void
    {
        if (!$this->status->canCapture()) {
            throw new LogicException(
                "Cannot capture Attractor [{$this->id}]: status [{$this->status->value}]"
            );
        }

        $this->status = AttractorStatus::CAPTURED;
    }

    public function escape(): void
    {
        if (!$this->status->canEscape()) {
            throw new LogicException(
                "Cannot escape Attractor [{$this->id}]: status [{$this->status->value}]"
            );
        }

        $this->status = AttractorStatus::ESCAPED;
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
    // Private
    // ──────────────────────────────────────────

    private function extractDimension(WorldStateVector $state, string $dimension): float
    {
        return match ($dimension) {
            'entropy' => $state->entropy,
            'order' => $state->order,
            'cohesion' => $state->cohesion,
            'innovation' => $state->innovation,
            'inequality' => $state->inequality,
            'legitimacy' => $state->legitimacy,
            'trauma' => $state->trauma,
            default => 0.0,
        };
    }
}
