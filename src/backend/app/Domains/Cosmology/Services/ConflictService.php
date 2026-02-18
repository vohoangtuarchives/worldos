<?php

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\Entities\Universe;
use App\Models\UniverseModel;
use App\Models\CosmicFaction;

class ConflictService
{
    private const RIVALRIES = [
        'HARMONY' => ['CHAOS', 'NIHILISM'],
        'CHAOS' => ['HARMONY', 'LOGIC'],
        'NIHILISM' => ['HARMONY', 'LOGIC'],
        'LOGIC' => ['CHAOS', 'NIHILISM']
    ];

    private const FRICTION_RADIUS = 300; // Radius for inter-faction tension

    public function applyFriction(Universe $universe, array $activeUniverses): Universe
    {
        $currentFactionId = $universe->getCosmicFactionId();
        if (!$currentFactionId) return $universe;

        $currentFaction = CosmicFaction::find($currentFactionId);
        if (!$currentFaction) return $universe;

        $myIdeology = $currentFaction->ideology;
        $rivalIdeologies = self::RIVALRIES[$myIdeology] ?? [];

        $frictionIntensity = 0.0;
        $myCoords = $universe->getCoords() ?? ['x' => 0, 'y' => 0, 'z' => 0];

        foreach ($activeUniverses as $model) {
            if ($model->id === $universe->getId()) continue;
            if (!$model->cosmic_faction_id || $model->cosmic_faction_id === $currentFactionId) continue;

            $otherFaction = $model->cosmicFaction;
            if (!$otherFaction || !in_array($otherFaction->ideology, $rivalIdeologies)) continue;

            $otherCoords = $model->coords ?? ['x' => 0, 'y' => 0, 'z' => 0];
            $distance = sqrt(
                pow($myCoords['x'] - $otherCoords['x'], 2) +
                pow($myCoords['y'] - $otherCoords['y'], 2) +
                pow($myCoords['z'] - $otherCoords['z'], 2)
            );

            if ($distance < self::FRICTION_RADIUS) {
                // Inverse linear friction based on distance
                $intensity = (self::FRICTION_RADIUS - $distance) / self::FRICTION_RADIUS;
                $frictionIntensity += $intensity * 0.05; // 5% max impact per neighbor
            }
        }

        if ($frictionIntensity > 0) {
            $state = $universe->getState();
            $newCohesion = max(0.0, $state->getCohesion() - ($frictionIntensity * 0.1));
            $newResource = max(0.0, $state->getResourceStock() - ($frictionIntensity * 0.05));
            $newTrauma = min(1.0, $state->getTrauma() + ($frictionIntensity * 0.02));

            $newVector = \App\Domains\Cosmology\Entities\WorldStateVector::create(
                $state->getOrder(),
                $state->getEntropy(),
                $newCohesion,
                $state->getLegitimacy(),
                $state->getInnovation(),
                $state->getMilitary(),
                $state->getInequality(),
                $newTrauma,
                $state->getEliteCohesion(),
                $newResource
            );

            return new Universe($newVector, $universe->getParameters(), $universe->getId(), $universe->getAge(), $universe->getCoords(), $universe->getCosmicFactionId());
        }

        return $universe;
    }
}
