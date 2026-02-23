<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

use WorldOS\Legacy\Domain\Cosmic\ValueObject\Attractor;

/**
 * AttractorIncarnation — versioned snapshot of an attractor's state during a period in the simulation.
 */
final class AttractorIncarnation
{
    public function __construct(
        public readonly string $id,
        public readonly string $attractorId,
        public readonly ?string $parentIncarnationId,
        public readonly int $startTick,
        public readonly ?int $endTick,
        public readonly array $centroidSnapshot,
        public readonly array $semanticSnapshot,
        public readonly float $basinRadius,
        public readonly float $curvatureFactor,
        public readonly float $rebirthGainFromParent,
        public readonly float $morphIntensity
    ) {}

    public static function fromAttractor(Attractor $attractor, int $startTick = 0): self
    {
        $code = $attractor->code;
        $centroid = [
            'entropy' => $attractor->equilibriumEntropy,
            'energy' => $attractor->equilibriumEnergy,
            'stability' => 0.7,
            'strain' => 0.1,
            'causality' => 0.5,
        ];
        $order = 1.0 - $attractor->equilibriumEntropy;
        $chaos = $attractor->equilibriumEntropy;
        return new self(
            id: $code . '_inc_' . $startTick,
            attractorId: $code,
            parentIncarnationId: null,
            startTick: $startTick,
            endTick: null,
            centroidSnapshot: $centroid,
            semanticSnapshot: ['theme' => $code, 'archetype' => 'REGIME', 'mood' => 'NEUTRAL', 'order' => $order, 'chaos' => $chaos],
            basinRadius: 0.3,
            curvatureFactor: 1.0,
            rebirthGainFromParent: 0.0,
            morphIntensity: 0.0
        );
    }

    public function createChild(
        int $childIndex,
        int $startTick,
        array $newCentroid,
        array $newSemantic,
        float $rebirthGain,
        float $morphIntensity
    ): self {
        $elasticity = min(0.1, $rebirthGain * 0.05);
        $newRadius = min(0.6, $this->basinRadius * (1.0 + $elasticity));
        return new self(
            id: $this->attractorId . '_inc_' . $childIndex,
            attractorId: $this->attractorId,
            parentIncarnationId: $this->id,
            startTick: $startTick,
            endTick: null,
            centroidSnapshot: $newCentroid,
            semanticSnapshot: $newSemantic,
            basinRadius: $newRadius,
            curvatureFactor: $this->curvatureFactor,
            rebirthGainFromParent: $rebirthGain,
            morphIntensity: $morphIntensity
        );
    }

    public function close(int $endTick): self
    {
        return new self(
            id: $this->id,
            attractorId: $this->attractorId,
            parentIncarnationId: $this->parentIncarnationId,
            startTick: $this->startTick,
            endTick: $endTick,
            centroidSnapshot: $this->centroidSnapshot,
            semanticSnapshot: $this->semanticSnapshot,
            basinRadius: $this->basinRadius,
            curvatureFactor: $this->curvatureFactor,
            rebirthGainFromParent: $this->rebirthGainFromParent,
            morphIntensity: $this->morphIntensity
        );
    }

    /** When endTick is set, returns duration; when open, pass currentTick to get elapsed time. */
    public function lifespan(?int $currentTick = null): int
    {
        if ($this->endTick !== null) {
            return $this->endTick - $this->startTick;
        }
        if ($currentTick !== null) {
            return $currentTick - $this->startTick;
        }
        return 0;
    }

    public function isActive(int $currentTick): bool
    {
        if ($this->endTick === null) return $currentTick >= $this->startTick;
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
