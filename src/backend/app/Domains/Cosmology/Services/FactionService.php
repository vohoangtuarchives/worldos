<?php

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\Entities\Universe;
use App\Models\CosmicFaction;
use App\Models\UniverseModel;

class FactionService
{
    public const FACTION_CRYSTAL_ORDER = 'THE_CRYSTAL_ORDER';
    public const FACTION_ENTROPIC_SWARM = 'ENTROPIC_SWARM';
    public const FACTION_VOID_CULT = 'VOID_CULT';
    public const FACTION_TECHNOCRATIC_HEGEMONY = 'TECHNOCRATIC_HEGEMONY';

    public function getSuggestedFaction(Universe $universe): ?string
    {
        $state = $universe->getState();
        
        // Logical grouping
        if ($state->getOrder() > 0.8) return self::FACTION_CRYSTAL_ORDER;
        if ($state->getEntropy() > 0.8) return self::FACTION_ENTROPIC_SWARM;
        if ($state->getTrauma() > 0.7) return self::FACTION_VOID_CULT;
        if ($state->getInnovation() > 0.8) return self::FACTION_TECHNOCRATIC_HEGEMONY;

        return null;
    }

    public function ensureCommonFactionsExist(): void
    {
        $factions = [
            [
                'name' => 'The Crystal Order',
                'ideology' => 'HARMONY',
                'color' => '#00f2ff',
                'stats' => ['buff' => 'ORDER_RECOVERY', 'multiplier' => 1.1]
            ],
            [
                'name' => 'Entropic Swarm',
                'ideology' => 'CHAOS',
                'color' => '#ff0055',
                'stats' => ['buff' => 'INNOVATION_SPEED', 'multiplier' => 1.2]
            ],
            [
                'name' => 'Void Cult',
                'ideology' => 'NIHILISM',
                'color' => '#8800ff',
                'stats' => ['buff' => 'VOID_RESISTANCE', 'multiplier' => 1.0]
            ],
            [
                'name' => 'Technocratic Hegemony',
                'ideology' => 'LOGIC',
                'color' => '#ffff00',
                'stats' => ['buff' => 'RESOURCE_EFFICIENCY', 'multiplier' => 1.15]
            ]
        ];

        foreach ($factions as $f) {
            CosmicFaction::firstOrCreate(['name' => $f['name']], $f);
        }
    }

    public function applyFactionBuffs(Universe $universe): Universe
    {
        $model = UniverseModel::find($universe->getId());
        if (!$model || !$model->cosmic_faction_id) return $universe;

        $faction = $model->cosmicFaction;
        if (!$faction) return $universe;

        $state = $universe->getState();
        $buff = $faction->stats['buff'] ?? null;
        $mult = $faction->stats['multiplier'] ?? 1.0;

        $newOrder = $state->getOrder();
        $newInnovation = $state->getInnovation();
        $newResilience = $state->getResourceStock();

        switch ($buff) {
            case 'ORDER_RECOVERY':
                $newOrder *= $mult;
                break;
            case 'INNOVATION_SPEED':
                $newInnovation *= $mult;
                break;
            case 'RESOURCE_EFFICIENCY':
                $newResilience *= $mult;
                break;
        }

        $newVector = \App\Domains\Cosmology\Entities\WorldStateVector::create(
            min(1.0, $newOrder),
            $state->getEntropy(),
            $state->getCohesion(),
            $state->getLegitimacy(),
            min(1.0, $newInnovation),
            $state->getMilitary(),
            $state->getInequality(),
            $state->getTrauma(),
            $state->getEliteCohesion(),
            min(1.0, $newResilience)
        );

        return new Universe($newVector, $universe->getParameters(), $universe->getId(), $universe->getAge(), $universe->getCoords());
    }

    public function joinFaction(Universe $universe, string $factionName): void
    {
        $faction = CosmicFaction::where('name', $factionName)->first();
        if (!$faction) return;

        UniverseModel::where('id', $universe->getId())->update([
            'cosmic_faction_id' => $faction->id
        ]);
    }

    public function applyEdicts(Universe $universe): Universe
    {
        $model = UniverseModel::find($universe->getId());
        if (!$model || !$model->cosmic_faction_id) return $universe;

        $faction = $model->cosmicFaction;
        if (!$faction || empty($faction->stats['active_edict'])) return $universe;

        $edict = $faction->stats['active_edict'];
        $state = $universe->getState();

        $newOrder = $state->getOrder();
        $newInequality = $state->getInequality();
        $newInnovation = $state->getInnovation();
        $newMilitary = $state->getMilitary();

        switch ($edict) {
            case 'TOTALITARIAN_STABILITY':
                $newOrder = min(1.0, $newOrder * 1.1 + 0.05);
                $newInequality = min(1.0, $newInequality * 1.2 + 0.05);
                break;
            case 'UNRESTRICTED_RESEARCH':
                $newInnovation = min(1.0, $newInnovation * 1.2 + 0.05);
                $newOrder = max(0.0, $newOrder * 0.95 - 0.02);
                break;
            case 'GALACTIC_MOBILIZATION':
                $newMilitary = min(1.0, $newMilitary * 1.25 + 0.1);
                $newInnovation = max(0.0, $newInnovation * 0.9 - 0.05);
                break;
        }

        $newVector = \App\Domains\Cosmology\Entities\WorldStateVector::create(
            $newOrder,
            $state->getEntropy(),
            $state->getCohesion(),
            $state->getLegitimacy(),
            $newInnovation,
            $newMilitary,
            $newInequality,
            $state->getTrauma(),
            $state->getEliteCohesion(),
            $state->getResourceStock()
        );

        return new Universe($newVector, $universe->getParameters(), $universe->getId(), $universe->getAge(), $universe->getCoords(), $universe->getCosmicFactionId());
    }
}
