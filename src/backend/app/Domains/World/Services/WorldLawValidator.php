<?php

namespace App\Domains\World\Services;

use WorldOS\Blueprint\Domain\Legacy\Enums\MagicSystemType;
use WorldOS\Blueprint\Domain\Legacy\Enums\PowerCeiling;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use App\Models\World;
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
            // 1. Resurrection Check
            if ($claim->type === 'RESURRECTION' && $profile->deathFinality === 'ABSOLUTE') { // Assuming logic needed here or defaulting
                // Note: We haven't fully implemented DeathFinality enum in Profile yet (it was 'Future' in DTO).
                // Let's assume default is NOT absolute for Xianxia, but if profile implies strictness...
                // For MVP, checking Magic/Cultivation is more critical.
            }

            // 2. Magic Check
            if ($claim->type === 'SPELL_CAST' && $profile->magicSystem === MagicSystemType::NONE) {
                $violations[] = "Magic spells are forbidden in this world.";
                $isValid = false;
            }

            // 3. Cultivation Check
            if ($claim->type === 'CULTIVATION_BREAKTHROUGH' && !$profile->cultivationAllowed) {
                $violations[] = "Cultivation is not allowed in this world.";
                $isValid = false;
            }

            // 4. Power Ceiling Check
            if ($claim->magnitude !== null && $this->exceedsPowerCeilingClaim($claim, $profile->powerCeiling)) {
                $violations[] = "Event magnitude ({$claim->magnitude}) exceeds world power ceiling.";
                $isValid = false;
            }

            // 5. Tech Check
             if ($claim->type === 'HIGH_TECH_USAGE' && $profile->techLevel->value !== 'FUTURISTIC' && $profile->techLevel->value !== 'MODERN') {
                $violations[] = "High technology is forbidden in this era.";
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function exceedsPowerCeilingClaim(Claim $claim, PowerCeiling $ceiling): bool
    {
         // 1-10 Scale matched to Enums
        $limit = match($ceiling) {
            PowerCeiling::HUMAN => 3,
            PowerCeiling::HUMAN_PLUS => 5,
            PowerCeiling::TRANSCENDENT => 8,
            PowerCeiling::IMMORTAL => 10,
        };
        return $claim->magnitude > $limit;
    }


    /**
     * Validate if a Myth is allowed to emerge in this world.
     */
    public function validateMythEmergence(WorldLawProfile $profile, float $beliefStrength): bool
    {
        if (!$profile->mythEmergenceEnabled) {
            return false;
        }

        return $beliefStrength >= ($profile->beliefToRealityRatio * 50); // Normalized threshold
    }

    /**
     * Clamp or Reject a Seed based on World Laws.
     * Returns the modified Seed, or null if rejected.
     */
    public function validateSeedApplication(WorldLawProfile $profile, Seed $seed): ?Seed
    {
        // 1. Check Magic System Compatibility
        if ($this->requiresMagic($seed) && $profile->magicSystem === MagicSystemType::NONE) {
            // Reject magic seeds in non-magic worlds
            return null;
        }

        // 2. Check Power Ceiling
        if ($this->exceedsPowerCeiling($seed, $profile->powerCeiling)) {
            // Clamp severity
            $seed->severity = min($seed->severity, 3); // Cap at low severity
        }

        // 3. Cultivation Checks
        if ($this->isCultivationRelated($seed) && !$profile->cultivationAllowed) {
            return null;
        }

        return $seed;
    }

    /**
     * Calculate increased/decreased influence for The Heavenly Way (Balancing).
     */
    public function getBalancingInfluence(WorldLawProfile $profile): float
    {
        return $profile->heavenlyWayStrength;
    }

    /**
     * Get system constraints for AI Prompt.
     */
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

    // --- Helpers ---

    protected function requiresMagic(Seed $seed): bool
    {
        // Simple heuristic based on seed type or dimension
        // In reality, we'd need metadata on the Seed class.
        // For now, assume 'MYSTERY' or 'ARTIFACT' might imply magic?
        // Let's assume 'ANCIENT_RUIN_DISCOVERY' requires magic if it's a "Mystical" ruin.
        // But for MVP, let's say NO seeds strictly require magic yet, 
        // unless we add specific ones like 'MANA_STORM'.
        return in_array($seed->type, ['MANA_STORM', 'SPIRIT_VEIN_ERUPTION']);
    }

    protected function isCultivationRelated(Seed $seed): bool
    {
        return in_array($seed->type, ['TRIBULATION', 'REALM_BREAKTHROUGH', 'SECT_TOURNAMENT']);
    }

    protected function exceedsPowerCeiling(Seed $seed, PowerCeiling $ceiling): bool
    {
        // 1-10 Scale
        $maxSeverity = match($ceiling) {
            PowerCeiling::HUMAN => 3,
            PowerCeiling::HUMAN_PLUS => 5,
            PowerCeiling::TRANSCENDENT => 8,
            PowerCeiling::IMMORTAL => 10,
        };

        return $seed->severity > $maxSeverity;
    }
}
