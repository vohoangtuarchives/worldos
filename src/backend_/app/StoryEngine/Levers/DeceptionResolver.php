<?php

namespace App\StoryEngine\Levers;

use App\StoryEngine\FactionState;
use App\StoryEngine\InformationSeed;

class DeceptionResolver
{
    /**
     * Faction A spreads misinformation to target Faction B (or general public).
     */
    public static function spread(
        FactionState $from,
        // Target could be specific or global
    ): InformationSeed {
        // Create a deceptive seed (Intel Report)
        return new InformationSeed(
            type: 'INTEL_REPORT',
            dimension: 'faction', // usually about another faction
            severity: 4,
            truthfulness: rand(2, 8) / 10, // 0.2 to 0.8 truth
            sourceFactionId: $from->id
        );
    }
}
