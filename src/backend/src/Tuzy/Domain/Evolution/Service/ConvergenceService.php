<?php

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\Universe;
use Tuzy\Domain\Evolution\ValueObject\WorldStateVector;
use Tuzy\Domain\Cosmology\Repository\CosmologyRepository;
use Illuminate\Support\Str;

class ConvergenceService
{
    private CosmologyRepository $repository;
    private LifecycleService $lifecycleService;

    public function __construct(CosmologyRepository $repository, LifecycleService $lifecycleService)
    {
        $this->repository = $repository;
        $this->lifecycleService = $lifecycleService;
    }

    /**
     * Merges two compatible universes into a new reality.
     */
    public function merge(Universe $u1, Universe $u2): Universe
    {
        $s1 = $u1->getState();
        $s2 = $u2->getState();

        // Calculate Merged State (Average + Bonus)
        $newOrder = ($s1->getOrder() + $s2->getOrder()) / 2;
        $newEntropy = ($s1->getEntropy() + $s2->getEntropy()) / 2;
        
        // Harmony Bonus to Cohesion
        $newCohesion = min(1, (($s1->getCohesion() + $s2->getCohesion()) / 2) * 1.1);
        
        $newLegitimacy = ($s1->getLegitimacy() + $s2->getLegitimacy()) / 2;
        $newInnovation = ($s1->getInnovation() + $s2->getInnovation()) / 2;
        $newMilitary = ($s1->getMilitary() + $s2->getMilitary()) / 2;
        $newInequality = ($s1->getInequality() + $s2->getInequality()) / 2;
        $newTrauma = ($s1->getTrauma() + $s2->getTrauma()) / 2;
        $newEliteCohesion = min(1, (($s1->getEliteCohesion() + $s2->getEliteCohesion()) / 2) * 1.05);
        $newResourceStock = ($s1->getResourceStock() + $s2->getResourceStock()) / 2;

        $newState = WorldStateVector::create(
            $newOrder,
            $newEntropy,
            $newCohesion,
            $newLegitimacy,
            $newInnovation,
            $newMilitary,
            $newInequality,
            $newTrauma,
            $newEliteCohesion,
            $newResourceStock
        );

        // Position: Average of parents
        $c1 = $u1->getCoords();
        $c2 = $u2->getCoords();
        $newCoords = [
            'x' => ($c1['x'] + $c2['x']) / 2,
            'y' => ($c1['y'] + $c2['y']) / 2,
            'z' => ($c1['z'] + $c2['z']) / 2,
        ];

        // Lineage tracking in parameters
        $parameters = [
            'ancestors' => [$u1->getId(), $u2->getId()],
            'event' => 'CONVERGENCE'
        ];

        $newUniverse = new Universe(
            $newState,
            $parameters,
            (string) Str::uuid(),
            max($u1->getAge(), $u2->getAge()), // Matures faster?
            $newCoords
        );

        // Save new universe
        $this->repository->save($newUniverse);

        // Archive parents
        $this->lifecycleService->archive($u1, 'CONVERGENCE');
        $this->lifecycleService->archive($u2, 'CONVERGENCE');

        return $newUniverse;
    }
}



