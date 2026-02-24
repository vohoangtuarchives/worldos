<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\StrategyVector;

class LegacyPropagation
{
    /**
     * Spawns a successor civilization from a collapsed empire.
     * Retains technological core but resets political structure and randomly mutates strategy.
     */
    public function spawnSuccessor(CivilizationSnapshot $fallenEmpire): CivilizationSnapshot
    {
        // Retain 80% of tech limit
        $newTechLevel = $fallenEmpire->technologicalLevel * 0.8;
        
        // Deep historical trauma and legacy memory
        $newLegacy = min(1.0, $fallenEmpire->historicalLegacy + 0.2);
        
        // Political authority shatters, military needs to be rebuilt, stability drops
        $newStability = 0.2;
        $newLegitimacy = 0.3;
        $newEliteCohesion = 0.1;
        $newMilitary = $fallenEmpire->militaryPressure * 0.3;

        // Clone current state as an array to modify
        $data = $fallenEmpire->toArray();
        
        $data['technological_level'] = $newTechLevel;
        $data['historical_legacy'] = $newLegacy;
        $data['stability'] = $newStability;
        $data['legitimacy'] = $newLegitimacy;
        $data['elite_cohesion'] = $newEliteCohesion;
        $data['military_pressure'] = $newMilitary;
        
        // Clear old trauma but set a deep metaphysical scar
        $data['residual'] = [
            'war_trauma' => 0.5, // Successors born from war/collapse
            'social_unrest' => 0.8,
            'metaphysical_scar' => 0.6,
            'cumulative_trauma' => ($data['residual']['cumulative_trauma'] ?? 0.0) + 1.0
        ];
        
        // New civilizations start fresh in terms of cycle energy 
        $data['resilience'] = 1.0;
        $data['internal_entropy'] = 0.1;

        return CivilizationSnapshot::fromArray($data);
    }
    
    /**
     * Mutates the fallen empire's strategy violently for the successor states.
     * Splinter factions often adopt opposing or extreme doctrines.
     */
    public function mutateStrategy(StrategyVector $oldStrategy): StrategyVector
    {
        // High evolution rate (mutation) for successor states
        $dynamics = new ReplicatorDynamics();
        
        // Pretend a fitness profile that favors random traits slightly just to drift it
        $randomFitness = [];
        foreach (StrategyVector::DIMENSIONS as $dim) {
            $randomFitness[$dim] = mt_rand() / mt_getrandmax() * 2.0; // 0 to 2 multiplier
        }
        
        return $dynamics->evolve($oldStrategy, $randomFitness, mutationRate: 0.5, dt: 1.0);
    }
}
