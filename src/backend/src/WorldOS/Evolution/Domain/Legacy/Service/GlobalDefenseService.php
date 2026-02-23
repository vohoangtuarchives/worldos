<?php

namespace WorldOS\Evolution\Domain\Legacy\Service;

use App\Models\MultiverseMeta;
use App\Models\UniverseModel;
use App\Models\CosmicFaction;
use WorldOS\Legacy\Domain\Cosmology\Repository\CosmologyRepository;

class GlobalDefenseService
{
    public function contribute(string $universeId, float $amount)
    {
        $repo = app(CosmologyRepository::class);
        $universe = $repo->find($universeId);
        if (!$universe) return null;

        $state = $universe->getState();
        if ($state->getResourceStock() < $amount) return false;

        // Drain resource
        $newVector = \WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector::create(
            $state->getOrder(),
            $state->getEntropy(),
            $state->getCohesion(),
            $state->getLegitimacy(),
            $state->getInnovation(),
            $state->getMilitary(),
            $state->getInequality(),
            $state->getTrauma(),
            $state->getEliteCohesion(),
            $state->getResourceStock() - $amount
        );

        $newUniverse = new \WorldOS\Evolution\Domain\Legacy\ValueObject\Universe(
            $newVector,
            $universe->getParameters(),
            $universe->getId(),
            $universe->getAge(),
            $universe->getCoords(),
            $universe->getCosmicFactionId()
        );
        $repo->save($newUniverse);

        // Boost shield
        $meta = MultiverseMeta::first();
        $meta->shield_level += ($amount * 0.1);
        $meta->save();

        return true;
    }
}



