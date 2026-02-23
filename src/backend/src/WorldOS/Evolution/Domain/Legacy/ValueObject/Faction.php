<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

/**
 * Faction - Represents a political power group within the civilization.
 */
final class Faction
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly IdeologyVector $ideology,
        public readonly float $powerShare,        // 0.0 to 1.0 (Percentage of total civilization power)
        public readonly float $cohesion,          // 0.0 to 1.0 (Internal stability of the faction)
        public readonly float $legitimacyClaim,   // 0.0 to 1.0 (How legitimate their claim to power is perceived)
        public readonly float $resourceControl = 0.0,
        public readonly float $militaryInfluence = 0.0
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ideology' => $this->ideology->toArray(),
            'power_share' => $this->powerShare,
            'cohesion' => $this->cohesion,
            'legitimacy_claim' => $this->legitimacyClaim,
            'resource_control' => $this->resourceControl,
            'military_influence' => $this->militaryInfluence,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? uniqid('fac_'),
            name: $data['name'] ?? 'Unknown Faction',
            ideology: IdeologyVector::fromArray($data['ideology'] ?? []),
            powerShare: (float)($data['power_share'] ?? 0.0),
            cohesion: (float)($data['cohesion'] ?? 0.5),
            legitimacyClaim: (float)($data['legitimacy_claim'] ?? 0.5),
            resourceControl: (float)($data['resource_control'] ?? 0.0),
            militaryInfluence: (float)($data['military_influence'] ?? 0.0)
        );
    }
}
