<?php

namespace App\Domain\Shared\Traits;

use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldLawProfile;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\Claim;
use WorldOS\Blueprint\Domain\Legacy\Enums\MagicSystemType;
use WorldOS\Blueprint\Domain\Legacy\Enums\PowerCeiling;
use App\Exceptions\World\WorldLawViolationException;
use App\StoryEngine\Seed;

trait ValidatesWorldLaws
{
    /**
     * Validate a set of Claims against the World Law Profile.
     */
    protected function validateClaims(WorldLawProfile $profile, array $claims, array &$violations = []): bool
    {
        $isValid = true;

        foreach ($claims as $claim) {
            if (!$this->validateSingleClaim($profile, $claim, $violations)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * Validate a single claim against world laws.
     */
    protected function validateSingleClaim(WorldLawProfile $profile, Claim $claim, array &$violations = []): bool
    {
        // Magic System Check
        if ($claim->type === 'SPELL_CAST' && $profile->magicSystem === MagicSystemType::NONE) {
            $violations[] = "Magic spells are forbidden in this world.";
            return false;
        }

        // Cultivation Check
        if ($claim->type === 'CULTIVATION_BREAKTHROUGH' && !$profile->cultivationAllowed) {
            $violations[] = "Cultivation is not allowed in this world.";
            return false;
        }

        // Power Ceiling Check
        if ($claim->magnitude !== null && $this->exceedsPowerCeiling($claim->magnitude, $profile->powerCeiling)) {
            $violations[] = "Event magnitude ({$claim->magnitude}) exceeds world power ceiling.";
            return false;
        }

        // Technology Check
        if ($claim->type === 'HIGH_TECH_USAGE' && !$this->isTechLevelAllowed($profile->techLevel->value)) {
            $violations[] = "High technology is forbidden in this era.";
            return false;
        }

        return true;
    }

    /**
     * Validate if a Seed can be applied according to world laws.
     */
    protected function validateSeed(WorldLawProfile $profile, Seed $seed): ?Seed
    {
        // Check Magic System Compatibility
        if ($this->seedRequiresMagic($seed) && $profile->magicSystem === MagicSystemType::NONE) {
            throw WorldLawViolationException::magicViolation($seed->type, ['seed_type' => $seed->type]);
        }

        // Check Power Ceiling
        if ($this->exceedsPowerCeiling($seed->severity, $profile->powerCeiling)) {
            // Clamp severity instead of rejecting
            $seed->severity = $this->clampSeverityToCeiling($seed->severity, $profile->powerCeiling);
        }

        // Cultivation Checks
        if ($this->seedIsCultivationRelated($seed) && !$profile->cultivationAllowed) {
            throw WorldLawViolationException::cultivationForbidden($seed->type, ['seed_type' => $seed->type]);
        }

        return $seed;
    }

    /**
     * Check if magnitude exceeds power ceiling.
     */
    protected function exceedsPowerCeiling(int $magnitude, PowerCeiling $ceiling): bool
    {
        $limit = match($ceiling) {
            PowerCeiling::HUMAN => 3,
            PowerCeiling::HUMAN_PLUS => 5,
            PowerCeiling::TRANSCENDENT => 8,
            PowerCeiling::IMMORTAL => 10,
        };

        return $magnitude > $limit;
    }

    /**
     * Check if technology level allows high tech usage.
     */
    protected function isTechLevelAllowed(string $techLevel): bool
    {
        return in_array($techLevel, ['FUTURISTIC', 'MODERN']);
    }

    /**
     * Check if seed requires magic system.
     */
    protected function seedRequiresMagic(Seed $seed): bool
    {
        return in_array($seed->type, ['MANA_STORM', 'SPIRIT_VEIN_ERUPTION', 'MAGICAL_ARTIFACT']);
    }

    /**
     * Check if seed is cultivation related.
     */
    protected function seedIsCultivationRelated(Seed $seed): bool
    {
        return in_array($seed->type, ['TRIBULATION', 'REALM_BREAKTHROUGH', 'SECT_TOURNAMENT', 'CULTIVATION_BREAKTHROUGH']);
    }

    /**
     * Clamp severity to power ceiling.
     */
    protected function clampSeverityToCeiling(int $severity, PowerCeiling $ceiling): int
    {
        $maxSeverity = match($ceiling) {
            PowerCeiling::HUMAN => 3,
            PowerCeiling::HUMAN_PLUS => 5,
            PowerCeiling::TRANSCENDENT => 8,
            PowerCeiling::IMMORTAL => 10,
        };

        return min($severity, $maxSeverity);
    }

    /**
     * Get system constraints for AI prompts.
     */
    protected function getSystemConstraints(WorldLawProfile $profile): string
    {
        return "WORLD LAWS (ABSOLUTE):\n" .
               "- Magic System: {$profile->magicSystem->value}\n" .
               "- Power Ceiling: {$profile->powerCeiling->value}\n" .
               "- Cultivation: " . ($profile->cultivationAllowed ? 'Allowed' : 'Forbidden') . "\n" .
               "- Tech Level: {$profile->techLevel->value}\n" .
               "- Death: Final (unless stated otherwise)\n" .
               "DO NOT violate these laws.";
    }

    /**
     * Validate myth emergence against world laws.
     */
    protected function validateMythEmergence(WorldLawProfile $profile, float $beliefStrength): bool
    {
        if (!$profile->mythEmergenceEnabled) {
            throw WorldLawViolationException::mythEmergenceBlocked(
                'Unknown',
                $beliefStrength,
                $profile->beliefToRealityRatio * 50,
                ['reason' => 'Myth emergence disabled']
            );
        }

        $threshold = $profile->beliefToRealityRatio * 50;
        if ($beliefStrength < $threshold) {
            throw WorldLawViolationException::mythEmergenceBlocked(
                'Unknown',
                $beliefStrength,
                $threshold,
                ['reason' => 'Insufficient belief strength']
            );
        }

        return true;
    }
}
