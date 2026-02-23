<?php

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\Universe;
use App\Models\Fleet;
use WorldOS\Legacy\Domain\Cosmology\Repository\CosmologyRepository;

class GalacticWarfareService
{
    protected CosmologyRepository $repository;

    public function __construct(CosmologyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Convert resources into a military fleet.
     */
    public function buildFleet(Universe $universe, string $name, float $resourceCost): Fleet
    {
        // Check local resources
        $resources = $universe->stateVector->dimensions['resource_stock'] ?? 0;
        
        if ($resources < $resourceCost) {
            throw new \Exception("Insufficient resources to build fleet. Required: {$resourceCost}, Available: {$resources}");
        }

        // Deduct resources
        // Note: This requires updating the Universe state vector, which is immutable-ish in our domain design
        // We need to clone and save.
        
        // Update vector
        $newVector = clone $universe->stateVector;
        $newVector->dimensions['resource_stock'] -= $resourceCost;
        // Boost military dimension slightly
        $newVector->dimensions['military'] += ($resourceCost * 0.1); 

        // Save universe state
        $universe->stateVector = $newVector;
        $this->repository->save($universe);

        // Create Fleet
        $fleet = Fleet::create([
            'universe_id' => $universe->id,
            'name' => $name,
            'strength' => $resourceCost * 10, // 1 Resource = 10 Fleet Power
            'status' => 'IDLE',
            'coordinates' => $universe->parameters['coords'] ?? ['x'=>0, 'y'=>0, 'z'=>0], // Spawn at home
        ]);

        return $fleet;
    }

    /**
     * Send a fleet to another universe.
     */
    public function mobilizeFleet(Fleet $fleet, string $destinationUniverseId): Fleet
    {
        $fleet->destination_universe_id = $destinationUniverseId;
        $fleet->status = 'MOVING';
        $fleet->save();

        return $fleet;
    }

    /**
     * Calculate battle outcome between two fleets.
     * Returns the winner.
     */
    public function resolveCombat(Fleet $attacker, Fleet $defender): Fleet
    {
        // Simple distinct logic
        $attackerRoll = rand(0, 100) + $attacker->strength;
        $defenderRoll = rand(0, 100) + $defender->strength;

        if ($attackerRoll > $defenderRoll) {
            $defender->delete();
            $attacker->strength -= ($defender->strength * 0.2); // Damage
            $attacker->status = 'IDLE'; // Victorious
            $attacker->save();
            return $attacker;
        } else {
            $attacker->delete();
            $defender->strength -= ($attacker->strength * 0.2); // Damage
            $defender->save();
            return $defender;
        }
    }
}



