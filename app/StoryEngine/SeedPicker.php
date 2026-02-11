<?php

namespace App\StoryEngine;

class SeedPicker
{
    /**
     * @param Seed[] $seeds
     * @return Seed|null
     */
    public static function pick(array $seeds): ?Seed
    {
        if (empty($seeds)) {
            return null;
        }

        // Sort by score descending (Highest Priority)
        // Score = Severity + Age
        usort($seeds, fn (Seed $a, Seed $b) => $b->score() <=> $a->score());

        return $seeds[0];
    }
}
