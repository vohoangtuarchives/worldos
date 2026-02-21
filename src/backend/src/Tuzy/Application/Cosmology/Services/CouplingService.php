<?php

namespace Tuzy\Application\Cosmology\Services;

use Tuzy\Application\Cosmology\Entities\Universe;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;

class CouplingService
{
    /**
     * Calculate Euclidean distance between two universe state vectors.
     * Dimensions: Order, Entropy, Innovation, Military, Inequality, Trauma.
     */
    public function calculateDistance(Universe $u1, Universe $u2): float
    {
        $v1 = $u1->getState();
        $v2 = $u2->getState();

        $sumSq = 0.0;
        
        $dims = [
            $v1->getOrder() - $v2->getOrder(),
            $v1->getEntropy() - $v2->getEntropy(),
            $v1->getInnovation() - $v2->getInnovation(),
            $v1->getMilitary() - $v2->getMilitary(),
            $v1->getInequality() - $v2->getInequality(),
            $v1->getTrauma() - $v2->getTrauma(),
        ];

        foreach ($dims as $d) {
            $sumSq += $d * $d;
        }

        return sqrt($sumSq);
    }

    public function calculateSpatialDistance(Universe $u1, Universe $u2): float
    {
        $c1 = $u1->getCoords() ?? ['x' => 0, 'y' => 0, 'z' => 0];
        $c2 = $u2->getCoords() ?? ['x' => 0, 'y' => 0, 'z' => 0];

        $dx = $c1['x'] - $c2['x'];
        $dy = $c1['y'] - $c2['y'];
        $dz = $c1['z'] - $c2['z'];

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * Interacts a universe with a set of neighbors.
     * Returns the new WorldStateVector if affected, null otherwise.
     */
    public function interact(Universe $target, array $neighbors, float $couplingStrength = 0.05): ?WorldStateVector
    {
        $affected = false;
        $targetState = $target->getState();
        $newEntropy = $targetState->getEntropy();
        
        // Trauma is derived from entropy bleed, but let's stick to simple logic
        $newTrauma = $targetState->getTrauma();

        foreach ($neighbors as $neighbor) {
            if ($neighbor->getId() === $target->getId()) continue;

            $spatialDistance = $this->calculateSpatialDistance($target, $neighbor);
            
            // Normalize spatial distance for the simulation
            // Simulation space is -1000 to 1000, max distance ~3464
            // We want resonance within range of ~200 units
            $normalizedDistance = $spatialDistance / 100.0; 

            $nState = $neighbor->getState();

            // Resonance Law: High Entropy neighbors bleed chaos if spatially close
            if ($normalizedDistance < 5.0 && $nState->getEntropy() > 0.6) {
                // Bleed effect
                $bleed = $couplingStrength * ($nState->getEntropy() - $targetState->getEntropy());
                if ($bleed > 0) {
                    // Inverse square law for spatial coupling
                    $bleed /= ($normalizedDistance * $normalizedDistance + 0.1);
                    $newEntropy += $bleed;
                    $newTrauma += $bleed * 0.1;
                    $affected = true;
                }
            }
        }

        if ($affected) {
            $s = $targetState;
            return WorldStateVector::create(
                $s->getOrder(),
                $newEntropy,
                $s->getCohesion(),
                $s->getLegitimacy(),
                $s->getInnovation(),
                $s->getMilitary(),
                $s->getInequality(),
                $newTrauma,
                $s->getEliteCohesion(),
                $s->getResourceStock()
            );
        }

        return null;
    }
}
