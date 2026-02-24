<?php

namespace App\StoryEngine;

class SeedTransition
{
    public const TYPE_POWER_GAP = 'POWER_GAP';
    public const TYPE_RESOURCE_SCARCITY = 'RESOURCE_SCARCITY';
    public const TYPE_SOCIAL_PRESSURE = 'SOCIAL_PRESSURE';
    public const TYPE_HIDDEN_KNOWLEDGE = 'HIDDEN_KNOWLEDGE';
    public const TYPE_LAW_CONFLICT = 'LAW_CONFLICT';
    public const TYPE_PUBLIC_EXPOSURE = 'PUBLIC_EXPOSURE';
    public const TYPE_ESCALATION_DEBT = 'ESCALATION_DEBT';
    public const TYPE_WORLD_ANOMALY = 'WORLD_ANOMALY';

    /**
     * @return Seed[]
     */
    public static function resolve(Seed $seed): array
    {
        $newSeeds = [];
        $dimLevel = $seed->getDimensionLevel();

        switch ($seed->type) {
            case self::TYPE_POWER_GAP:
                // RESOLVE -> IF dim < faction (2) -> POWER_GAP (dim+1, sev+1)
                // ELSE -> ESCALATION_DEBT | SOCIAL_PRESSURE
                if ($dimLevel < 2) {
                    $newDim = Seed::getDimensionFromLevel($dimLevel + 1);
                    $newSeeds[] = new Seed(self::TYPE_POWER_GAP, $newDim, $seed->severity + 1);
                    // Branching: Ambition causes friction
                    if (rand(0, 1) === 0) {
                         $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, $seed->dimension, 1);
                    }
                } else {
                    if (rand(0, 1) === 0) {
                         // 50% chance for Escalation Debt
                        $newSeeds[] = new Seed(self::TYPE_ESCALATION_DEBT, $seed->dimension, $seed->severity);
                        // Debt causes scarcity
                        $newSeeds[] = new Seed(self::TYPE_RESOURCE_SCARCITY, $seed->dimension, max(1, $seed->severity - 2));
                    } else {
                        $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, $seed->dimension, $seed->severity);
                        // Pressure causes law conflict
                         if (rand(0, 1) === 0) {
                             $newSeeds[] = new Seed(self::TYPE_LAW_CONFLICT, $seed->dimension, 1);
                         }
                    }
                }
                break;

            case self::TYPE_RESOURCE_SCARCITY:
                // RESOLVE -> SOCIAL_PRESSURE OR POWER_GAP (same dim)
                if (rand(0, 1) === 0) {
                    $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, $seed->dimension, $seed->severity);
                } else {
                    $newSeeds[] = new Seed(self::TYPE_POWER_GAP, $seed->dimension, $seed->severity);
                }
                break;

            case self::TYPE_SOCIAL_PRESSURE:
                // RESOLVE -> IF dim < CITY (3) -> LAW_CONFLICT
                // ELSE -> PUBLIC_EXPOSURE
                if ($dimLevel < 3) {
                     $newSeeds[] = new Seed(self::TYPE_LAW_CONFLICT, $seed->dimension, $seed->severity);
                     // Chance to spawn a secondary problem
                     if (rand(0, 2) === 0) {
                        $newSeeds[] = new Seed(self::TYPE_RESOURCE_SCARCITY, $seed->dimension, max(1, $seed->severity - 1));
                     }
                } else {
                     $newSeeds[] = new Seed(self::TYPE_PUBLIC_EXPOSURE, $seed->dimension, $seed->severity + 1);
                }
                break;

            case self::TYPE_HIDDEN_KNOWLEDGE:
                 // RESOLVE -> POWER_GAP OR RESOURCE_SCARCITY AND maybe SOCIAL_PRESSURE
                 if (rand(0, 1) === 0) {
                      $newSeeds[] = new Seed(self::TYPE_POWER_GAP, $seed->dimension, $seed->severity);
                 } else {
                      $newSeeds[] = new Seed(self::TYPE_RESOURCE_SCARCITY, $seed->dimension, $seed->severity);
                 }
                 if ($dimLevel > 0 && rand(0, 2) === 0) {
                     $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, $seed->dimension, $seed->severity);
                 }
                 break;

            case self::TYPE_LAW_CONFLICT:
                // RESOLVE -> SOCIAL_PRESSURE (dim+1) OR ESCALATION_DEBT
                if (rand(0, 1) === 0 && $dimLevel < 4) {
                     $newDim = Seed::getDimensionFromLevel($dimLevel + 1);
                     $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, $newDim, $seed->severity + 1);
                } else {
                     $newSeeds[] = new Seed(self::TYPE_ESCALATION_DEBT, $seed->dimension, $seed->severity);
                     // Law conflict often leaves a scar -> Hidden Knowledge
                     if (rand(0, 2) === 0) {
                         $newSeeds[] = new Seed(self::TYPE_HIDDEN_KNOWLEDGE, $seed->dimension, max(1, $seed->severity));
                     }
                }
                break;

            case self::TYPE_PUBLIC_EXPOSURE:
                // RESOLVE -> WORLD_ANOMALY
                 $newSeeds[] = new Seed(self::TYPE_WORLD_ANOMALY, 'city', $seed->severity + 1);
                 break;

            case self::TYPE_ESCALATION_DEBT:
                // RESOLVE -> POWER_GAP (allows breaking ceiling)
                $newSeeds[] = new Seed(self::TYPE_POWER_GAP, $seed->dimension, $seed->severity);
                // Debt resolution is messy
                $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, $seed->dimension, max(1, floor($seed->severity / 2)));
                break;

            case self::TYPE_WORLD_ANOMALY:
                 // RESOLVE -> POWER_GAP (dim = WORLD)
                 $newSeeds[] = new Seed(self::TYPE_POWER_GAP, 'world', 10);
                 break;
            default:
                 // Fallback to prevent dead ends
                 $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, 'personal', 1);
                 break;
        }

        // Rule Requirement: MUST generate >= 1 new seed
        if (empty($newSeeds)) {
             $newSeeds[] = new Seed(self::TYPE_SOCIAL_PRESSURE, 'personal', 1);
        }

        return $newSeeds;
    }
}
