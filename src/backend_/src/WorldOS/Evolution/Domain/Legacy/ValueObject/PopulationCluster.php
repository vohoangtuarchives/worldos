<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

/**
 * PopulationCluster - Represents a distribution of people holding a specific ideology,
 * acting as a "Historical Residue" from past events.
 */
final class PopulationCluster
{
    public function __construct(
        public readonly IdeologyVector $ideology,
        public readonly float $share,             // 0.0 to 1.0 (Percentage of total population)
        public readonly float $radicalization,    // 0.0 to 1.0 (Rigidity and extremeness of belief)
        public readonly ?string $originEventType = null, // e.g., 'WAR', 'REFORM_FAIL', 'ELITE_SPLIT'
        public readonly int $birthTick = 0        // The tick/year when this cluster was spawned
    ) {
    }

    public function withDrift(IdeologyVector $newIdeology, float $radicalizationDelta): self
    {
        return new self(
            ideology: $newIdeology,
            share: $this->share,
            radicalization: max(0.0, min(1.0, $this->radicalization + $radicalizationDelta)),
            originEventType: $this->originEventType,
            birthTick: $this->birthTick
        );
    }

    public function withShare(float $newShare): self
    {
        return new self(
            ideology: $this->ideology,
            share: max(0.0, min(1.0, $newShare)),
            radicalization: $this->radicalization,
            originEventType: $this->originEventType,
            birthTick: $this->birthTick
        );
    }

    public function toArray(): array
    {
        return [
            'ideology' => $this->ideology->toArray(),
            'share' => $this->share,
            'radicalization' => $this->radicalization,
            'origin_event_type' => $this->originEventType,
            'birth_tick' => $this->birthTick,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ideology: IdeologyVector::fromArray($data['ideology'] ?? []),
            share: (float)($data['share'] ?? 0.0),
            radicalization: (float)($data['radicalization'] ?? 0.0),
            originEventType: $data['origin_event_type'] ?? null,
            birthTick: (int)($data['birth_tick'] ?? 0)
        );
    }
}
