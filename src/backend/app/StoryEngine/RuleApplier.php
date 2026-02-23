<?php

namespace App\StoryEngine;

class RuleApplier
{
    /**
     * Apply rules to update World/Character state and filter new seeds
     * @param WorldState $world
     * @param CharacterState $character
     * @param Seed[] $activeSeeds (Reference)
     * @param Seed[] $newSeeds
     * @param Seed $resolvedSeed
     * @param \WorldOS\World\Application\Services\WorldLawValidator $validator
     */
    public static function apply(WorldState $world, CharacterState $character, array &$activeSeeds, array $newSeeds, Seed $resolvedSeed, \WorldOS\World\Application\Services\WorldLawValidator $validator): void
    {
        // 1. World Friction: If power related resolved -> increase public awareness
        if (in_array($resolvedSeed->type, [SeedTransition::TYPE_POWER_GAP, SeedTransition::TYPE_ESCALATION_DEBT])) {
            $world->publicAwareness += rand(1, 3);
        }

        // 2. Add new seeds with Dimension Unique Check
        foreach ($newSeeds as $newSeed) {
            // ADR-0004: Validate Seed against World Law
            $validatedSeed = $validator->validateSeedApplication($world->lawProfile, $newSeed);
            if (!$validatedSeed) {
                // Rejected by World Law
                continue;
            }
            $newSeed = $validatedSeed; // Use the clamped/modified seed

            $exists = false;
            foreach ($activeSeeds as $existing) {
                if ($existing->type === $newSeed->type && $existing->dimension === $newSeed->dimension) {
                    $exists = true;
                    // Rule 4 with Merge: 
                    $currentLevel = $existing->getDimensionLevel();
                    if ($currentLevel < 4) {
                         // Escalate Dimension
                         $newSeed->dimension = Seed::getDimensionFromLevel($currentLevel + 1);
                         $newSeed->severity += 1;
                         // Recursively checks? No, just add scalar. 
                         // But wait, what if the escalated DIMENSION also exists?
                         // For simplicity, we add it, and let next loop sort it out (Simulator allows 7 seeds)
                         // But we must add it to activeSeeds
                         $activeSeeds[] = $newSeed;
                    } else {
                         // Max Dimension -> Merge Severity
                         $existing->severity += $newSeed->severity;
                         // Don't add newSeed
                    }
                    break;
                }
            }
            if (!$exists) {
                $activeSeeds[] = $newSeed;
            }
        }

        // 3. Active Seed Limit (Rule 10): Keep max 7
        // Remove lowest score seeds
        if (count($activeSeeds) > 7) {
            usort($activeSeeds, fn (Seed $a, Seed $b) => $b->score() <=> $a->score()); // Descending
            $activeSeeds = array_slice($activeSeeds, 0, 7);
        }

        // 4. Update Character Power Tier (Mock logic for plateau)
        // Require higher severity threshold to level up (Escalation)
        // And resolving ESCALATION_DEBT specifically helps.
        
        $threshold = 15;
        if ($resolvedSeed->type === SeedTransition::TYPE_ESCALATION_DEBT) {
             $threshold = 10; // Easier to level up if paying debt
        }

        if ($resolvedSeed->type === SeedTransition::TYPE_POWER_GAP && $resolvedSeed->severity > $threshold) {
             // CHECK: Minimum Chapters Rule
             // Tier 0-10: needs 50 chapters
             // Tier 10+: needs 100 chapters
             $minChapters = $character->powerTier < 10 ? 50 : 100;

             if ($character->chaptersInCurrentTier >= $minChapters) {
                 $character->powerTier += 1;
                 $character->chaptersInCurrentTier = 0; // Reset counter
                 
                 // Reset severity of similar seeds to prevent rapid fire
                 foreach($activeSeeds as $s) {
                     if ($s->type === SeedTransition::TYPE_POWER_GAP) {
                         $s->severity = max(1, floor($s->severity / 2));
                     }
                 }
             } else {
                 // Not enough chapters, just dampen severity to keep excessive pressure waiting
                 // But don't reset fully, so it stays urgent
                 $resolvedSeed->severity = $threshold; 
             }
        } elseif ($resolvedSeed->type === SeedTransition::TYPE_ESCALATION_DEBT && $resolvedSeed->severity > $threshold) {
              // Debt can break ceiling if urgent enough, but still respect physics slightly
              if ($character->chaptersInCurrentTier >= 20) { // Debt allows faster ascension but not instant
                  $character->powerTier += 1;
                  $character->chaptersInCurrentTier = 0;
              }
        }
    }
}
