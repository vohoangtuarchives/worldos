<?php

namespace WorldOS\Legacy\Application\Cosmology\Services;

use App\Models\MultiverseMeta;
use WorldOS\Legacy\Application\Cosmology\Entities\Universe;

class HarbingerService
{
    public function processGlobalThreat(array $activeUniverses)
    {
        $meta = MultiverseMeta::first() ?? MultiverseMeta::create(['entropy_leak' => 0, 'shield_level' => 0, 'void_zones' => []]);
        
        $leakIncrease = 0.01; // Base leak per cycle
        
        foreach ($activeUniverses as $u) {
            $state = $u instanceof Universe ? $u->getState() : null;
            if ($state && $state->getEntropy() > 0.8) {
                $leakIncrease += 0.005; // High entropy universes leak more
            }
        }

        // Shield suppresses leak
        $effectiveLeak = max(0, $leakIncrease - ($meta->shield_level * 0.05));
        $meta->entropy_leak += $effectiveLeak;

        // Void Zone Spawning
        $zones = $meta->void_zones ?? [];
        if ($meta->entropy_leak > 1.0 && count($zones) < 5) {
            // Spawn a new Void Zone if leak is high enough
            $zones[] = [
                'id' => uniqid('void_'),
                'coords' => ['x' => rand(-1000, 1000), 'y' => rand(-1000, 1000), 'z' => rand(-1000, 1000)],
                'radius' => 200 + rand(0, 300),
                'intensity' => 0.1
            ];
            // Consumption of leak when a zone is born
            $meta->entropy_leak -= 0.5;
        }

        $meta->void_zones = $zones;
        $meta->save();

        return $meta;
    }

    public function applyVoidIncursion(Universe $u): Universe
    {
        $meta = MultiverseMeta::first();
        if (!$meta || empty($meta->void_zones)) return $u;

        $coords = $u->getCoords();
        $inRangeOfVoid = false;
        $maxIntensity = 0;

        foreach ($meta->void_zones as $zone) {
            $dist = sqrt(
                pow($coords['x'] - $zone['coords']['x'], 2) +
                pow($coords['y'] - $zone['coords']['y'], 2) +
                pow($coords['z'] - $zone['coords']['z'], 2)
            );

            if ($dist < $zone['radius']) {
                $inRangeOfVoid = true;
                $maxIntensity = max($maxIntensity, $zone['intensity']);
            }
        }

        if ($inRangeOfVoid) {
            $state = $u->getState();
            $newResource = max(0, $state->getResourceStock() - (0.05 * $maxIntensity));
            $newEntropy = min(1.0, $state->getEntropy() + (0.02 * $maxIntensity));
            
            $newVector = \WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector::create(
                $state->getOrder(),
                $newEntropy,
                $state->getCohesion(),
                $state->getLegitimacy(),
                $state->getInnovation(),
                $state->getMilitary(),
                $state->getInequality(),
                $state->getTrauma(),
                $state->getEliteCohesion(),
                $newResource
            );

            return new Universe(
                $newVector,
                $u->getParameters(),
                $u->getId(),
                $u->getAge(),
                $u->getCoords(),
                $u->getCosmicFactionId()
            );
        }

        return $u;
    }
}
