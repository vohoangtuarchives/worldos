<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;

use WorldOS\Evolution\Domain\Legacy\ValueObject\Attractor;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CosmicState;
use Illuminate\Support\Str;

/**
 * HeroAttractor
 * 
 * Spawns "Heroes" as systemic responses to civilization state conditions.
 * Heroes are emergent attractors that can stabilize or further disrupt the system.
 */
class HeroAttractor
{
    /**
     * Check if conditions are met to spawn a hero.
     */
    public function evaluateSpawn(CivilizationSnapshot $state, string $seed): ?array
    {
        $rng = hexdec(substr(md5($seed), 0, 8)) / 0xffffffff;
        
        // 1. Hero of Chaos (Entropy + Pressure)
        if ($state->stability > 0.05 && $state->internalEntropy > 0.7 && $state->militaryPressure > 0.5) {
            if ($rng < 0.25) {
                return $this->createHero($state, 'chaos_breaker', $seed);
            }
        }

        // 2. Hero of Prosperity (Golden Age peak)
        if ($state->prosperity > 0.8 && $state->stability > 0.8) {
            if ($rng < 0.1) {
                return $this->createHero($state, 'enlightened_sage', $seed);
            }
        }

        // 3. Hero of Survival (High external threat + resilience)
        if ($state->externalThreat > 0.7 && $state->resilience > 0.5) {
            if ($rng < 0.2) {
                return $this->createHero($state, 'national_defender', $seed);
            }
        }

        return null;
    }

    /**
     * Create a hero structure.
     */
    private function createHero(CivilizationSnapshot $state, string $archetype, string $seed): array
    {
        $heroSeed = $seed . "_hero_" . $archetype;
        $rng = hexdec(substr(md5($heroSeed), 0, 8)) / 0xffffffff;

        return [
            'type' => EventEngine::TYPE_HERO_BIRTH,
            'hero_id' => Str::uuid()->toString(),
            'archetype' => $archetype,
            'ambition' => round($rng * 0.5 + 0.5, 2), // High ambition (0.5 - 1.0)
            'charisma' => round((hexdec(substr(md5($heroSeed), 8, 4)) / 0xffff), 2),
            'military_skill' => round((hexdec(substr(md5($heroSeed), 12, 4)) / 0xffff), 2),
            'loyalty' => round((hexdec(substr(md5($heroSeed), 16, 4)) / 0xffff), 2),
            'narrative_template' => "hero_emerges_{$archetype}",
            'intensity' => round(0.5 + ($state->internalEntropy * 0.4), 2),
            'scale' => 5,
            'success' => true,
        ];
    }
}




