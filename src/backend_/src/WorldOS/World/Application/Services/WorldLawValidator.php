<?php

namespace WorldOS\World\Application\Services;

use WorldOS\Blueprint\Domain\Legacy\Enums\MagicSystemType;
use WorldOS\Blueprint\Domain\Legacy\Enums\PowerCeiling;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use App\StoryEngine\Seed;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\Claim;

class WorldLawValidator
{
    /**
     * Validate a set of Claims against the World Law Profile.
     * Returns true if ALL claims are valid.
     * Returns false if ANY claim is invalid.
     * 
     * @param WorldLawProfile $profile
     * @param Claim[] $claims
     * @param array &$violations Output array for violation messages
     */
    public function validateClaims(WorldLawProfile $profile, array $claims, array &$violations = []): bool
    {
        $isValid = true;

        foreach ($claims as $claim) {
            if ($claim->type === 'RESURRECTION' && $profile->deathFinality === 'ABSOLUTE') {
            }

            if ($claim->type === 'SPELL_CAST' && $profile->magicSystem === MagicSystemType::NONE) {
                $violations[] = "Magic spells are forbidden in this world.";
                $isValid = false;
            }

            if ($claim->type === 'CULTIVATION_BREAKTHROUGH' && !$profile->cultivationAllowed) {
                $violations[] = "Cultivation is not allowed in this world.";
                $isValid = false;
            }

            if ($claim->magnitude !== null && $this->exceedsPowerCeilingClaim($claim, $profile->powerCeiling)) {
                $violations[] = "Event magnitude ({$claim->magnitude}) exceeds world power ceiling.";
                $isValid = false;
            }

             if ($claim->type === 'HIGH_TECH_USAGE' && $profile->techLevel->value !== 'FUTURISTIC' && $profile->techLevel->value !== 'MODERN') {
                $violations[] = "High technology is forbidden in this era.";
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function exceedsPowerCeilingClaim(Claim $claim, PowerCeiling $ceiling): bool
    {
         $limit = match($ceiling) {
            PowerCeiling::HUMAN => 3,
            PowerCeiling::HUMAN_PLUS => 5,
            PowerCeiling::TRANSCENDENT => 8,
            PowerCeiling::IMMORTAL => 10,
        };
        return $claim->magnitude > $limit;
    }

    public function validateMythEmergence(WorldLawProfile $profile, float $beliefStrength): bool
    {
        if (!$profile->mythEmergenceEnabled) {
            return false;
        }

        return $beliefStrength >= ($profile->beliefToRealityRatio * 50);
    }

    public function validateSeedApplication(WorldLawProfile $profile, Seed $seed): ?Seed
    {
        if ($this->requiresMagic($seed) && $profile->magicSystem === MagicSystemType::NONE) {
            return null;
        }

        if ($this->exceedsPowerCeiling($seed, $profile->powerCeiling)) {
            $seed->severity = min($seed->severity, 3);
        }

        if ($this->isCultivationRelated($seed) && !$profile->cultivationAllowed) {
            return null;
        }

        return $seed;
    }

    public function getBalancingInfluence(WorldLawProfile $profile): float
    {
        return $profile->heavenlyWayStrength;
    }

    public function getSystemConstraints(WorldLawProfile $profile): string
    {
        return <<<EOT
WORLD LAWS (ABSOLUTE):
- Magic System: {$profile->magicSystem->value}
- Power Ceiling: {$profile->powerCeiling->value}
- Cultivation: {$profile->cultivationAllowed}
- Tech Level: {$profile->techLevel->value}
- Death: Final (unless stated otherwise).
DO NOT violate these laws.
EOT;
    }

    protected function requiresMagic(Seed $seed): bool
    {
        return in_array($seed->type, ['MANA_STORM', 'SPIRIT_VEIN_ERUPTION']);
    }

    protected function isCultivationRelated(Seed $seed): bool
    {
        return in_array($seed->type, ['TRIBULATION', 'REALM_BREAKTHROUGH', 'SECT_TOURNAMENT']);
    }

    protected function exceedsPowerCeiling(Seed $seed, PowerCeiling $ceiling): bool
    {
        $maxSeverity = match($ceiling) {
            PowerCeiling::HUMAN => 3,
            PowerCeiling::HUMAN_PLUS => 5,
            PowerCeiling::TRANSCENDENT => 8,
            PowerCeiling::IMMORTAL => 10,
        };

        return $seed->severity > $maxSeverity;
    }
}
