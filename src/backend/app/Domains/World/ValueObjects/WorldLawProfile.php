<?php

namespace App\Domains\World\ValueObjects;

use Tuzy\Domain\World\Enums\MagicSystemType;
use Tuzy\Domain\World\Enums\PowerCeiling;
use Tuzy\Domain\World\Enums\TechLevel;

class WorldLawProfile
{
    public function __construct(
        public MagicSystemType $magicSystem,
        public PowerCeiling $powerCeiling,
        public bool $cultivationAllowed,
        public bool $mythEmergenceEnabled,
        public float $beliefToRealityRatio, // 0.0 to 1.0
        public TechLevel $techLevel,
        public float $heavenlyWayStrength, // 0.0 to 2.0 (1.0 is standard)
        // Future: allowed_myth_types, death_finality
    ) {}

    public static function default(): self
    {
        return new self(
            magicSystem: MagicSystemType::SPIRITUAL_QI,
            powerCeiling: PowerCeiling::IMMORTAL,
            cultivationAllowed: true,
            mythEmergenceEnabled: true,
            beliefToRealityRatio: 0.8,
            techLevel: TechLevel::DYNASTIC,
            heavenlyWayStrength: 1.0
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            magicSystem: MagicSystemType::tryFrom($data['magic_system'] ?? '') ?? MagicSystemType::NONE,
            powerCeiling: PowerCeiling::tryFrom($data['power_ceiling'] ?? '') ?? PowerCeiling::HUMAN,
            cultivationAllowed: $data['cultivation_allowed'] ?? false,
            mythEmergenceEnabled: $data['myth_emergence_enabled'] ?? false,
            beliefToRealityRatio: (float) ($data['belief_to_reality_ratio'] ?? 0.0),
            techLevel: TechLevel::tryFrom($data['tech_level'] ?? '') ?? TechLevel::PRIMITIVE,
            heavenlyWayStrength: (float) ($data['heavenly_way_strength'] ?? 1.0)
        );
    }

    public function toArray(): array
    {
        return [
            'magic_system' => $this->magicSystem->value,
            'power_ceiling' => $this->powerCeiling->value,
            'cultivation_allowed' => $this->cultivationAllowed,
            'myth_emergence_enabled' => $this->mythEmergenceEnabled,
            'belief_to_reality_ratio' => $this->beliefToRealityRatio,
            'tech_level' => $this->techLevel->value,
            'heavenly_way_strength' => $this->heavenlyWayStrength,
        ];
    }
}
