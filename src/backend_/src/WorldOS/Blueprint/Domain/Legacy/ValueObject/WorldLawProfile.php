<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\ValueObject;

use WorldOS\Blueprint\Domain\Legacy\Enums\MagicSystemType;
use WorldOS\Blueprint\Domain\Legacy\Enums\PowerCeiling;
use WorldOS\Blueprint\Domain\Legacy\Enums\TechLevel;

final class WorldLawProfile
{
    public function __construct(
        public readonly MagicSystemType $magicSystem,
        public readonly PowerCeiling $powerCeiling,
        public readonly bool $cultivationAllowed,
        public readonly bool $mythEmergenceEnabled,
        public readonly float $beliefToRealityRatio,
        public readonly TechLevel $techLevel,
        public readonly float $heavenlyWayStrength,
        public readonly ?string $deathFinality = null,
    ) {
    }

    public static function default(): self
    {
        return new self(
            magicSystem: MagicSystemType::SPIRITUAL_QI,
            powerCeiling: PowerCeiling::IMMORTAL,
            cultivationAllowed: true,
            mythEmergenceEnabled: true,
            beliefToRealityRatio: 0.8,
            techLevel: TechLevel::DYNASTIC,
            heavenlyWayStrength: 1.0,
            deathFinality: null
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            magicSystem: MagicSystemType::tryFrom($data['magic_system'] ?? '') ?? MagicSystemType::NONE,
            powerCeiling: PowerCeiling::tryFrom($data['power_ceiling'] ?? '') ?? PowerCeiling::HUMAN,
            cultivationAllowed: (bool) ($data['cultivation_allowed'] ?? false),
            mythEmergenceEnabled: (bool) ($data['myth_emergence_enabled'] ?? false),
            beliefToRealityRatio: (float) ($data['belief_to_reality_ratio'] ?? 0.0),
            techLevel: TechLevel::tryFrom($data['tech_level'] ?? '') ?? TechLevel::PRIMITIVE,
            heavenlyWayStrength: (float) ($data['heavenly_way_strength'] ?? 1.0),
            deathFinality: isset($data['death_finality']) ? (string) $data['death_finality'] : null
        );
    }

    public function toArray(): array
    {
        $arr = [
            'magic_system' => $this->magicSystem->value,
            'power_ceiling' => $this->powerCeiling->value,
            'cultivation_allowed' => $this->cultivationAllowed,
            'myth_emergence_enabled' => $this->mythEmergenceEnabled,
            'belief_to_reality_ratio' => $this->beliefToRealityRatio,
            'tech_level' => $this->techLevel->value,
            'heavenly_way_strength' => $this->heavenlyWayStrength,
        ];
        if ($this->deathFinality !== null) {
            $arr['death_finality'] = $this->deathFinality;
        }
        return $arr;
    }

    public function getBeliefToRealityRatio(): float
    {
        return $this->beliefToRealityRatio;
    }

    public function isMythEmergenceEnabled(): bool
    {
        return $this->mythEmergenceEnabled;
    }

    public function equals(self $other): bool
    {
        return $this->magicSystem === $other->magicSystem
            && $this->powerCeiling === $other->powerCeiling
            && $this->cultivationAllowed === $other->cultivationAllowed
            && $this->mythEmergenceEnabled === $other->mythEmergenceEnabled
            && $this->beliefToRealityRatio === $other->beliefToRealityRatio
            && $this->techLevel === $other->techLevel
            && $this->heavenlyWayStrength === $other->heavenlyWayStrength
            && $this->deathFinality === $other->deathFinality;
    }
}
