<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\ValueObjects;

use WorldOS\Domains\Evolution\Enums\SocialClassType;

/**
 * AttractorIncarnation represents a versioned snapshot of an attractor's state
 * during a specific period in the simulation timeline.
 */
class AttractorIncarnation
{
    public function __construct(
        public readonly string $id,
        public readonly string $attractorId,
        public readonly ?string $parentIncarnationId,
        public readonly int $startTick,
        public readonly ?int $endTick,
        public readonly array $centroidSnapshot, // {entropy, energy, stability, ...}
        public readonly array $semanticSnapshot, // {theme, archetype, mood, ...}
        public readonly float $basinRadius,
        public readonly float $curvatureFactor,
        public readonly float $rebirthGainFromParent,
        public readonly float $morphIntensity
    ) {}

    public function isActive(int $currentTick): bool
    {
        if ($this->endTick === null) {
            return $currentTick >= $this->startTick;
        }

        return $currentTick >= $this->startTick && $currentTick <= $this->endTick;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'attractor_id' => $this->attractorId,
            'parent_incarnation_id' => $this->parentIncarnationId,
            'start_tick' => $this->startTick,
            'end_tick' => $this->endTick,
            'centroid_snapshot' => $this->centroidSnapshot,
            'semantic_snapshot' => $this->semanticSnapshot,
            'basin_radius' => $this->basinRadius,
            'curvature_factor' => $this->curvatureFactor,
            'rebirth_gain_from_parent' => $this->rebirthGainFromParent,
            'morph_intensity' => $this->morphIntensity,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            attractorId: $data['attractor_id'],
            parentIncarnationId: $data['parent_incarnation_id'] ?? null,
            startTick: $data['start_tick'],
            endTick: $data['end_tick'] ?? null,
            centroidSnapshot: $data['centroid_snapshot'],
            semanticSnapshot: $data['semantic_snapshot'] ?? [],
            basinRadius: $data['basin_radius'],
            curvatureFactor: $data['curvature_factor'],
            rebirthGainFromParent: $data['rebirth_gain_from_parent'] ?? 0.0,
            morphIntensity: $data['morph_intensity'] ?? 0.0
        );
    }
}


