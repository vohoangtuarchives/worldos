<?php

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\Repositories\CosmologyRepository;
use WorldOS\Domains\Evolution\ValueObjects\WorldStateVector;
use App\Models\UniverseModel;

class CrisisService
{
    private CosmologyRepository $repository;

    public function __construct(CosmologyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Triggers a localized crisis at a specific point in FieldSpace.
     * Affects all active universes within a given radius.
     */
    public function triggerRegionalCrisis(array $center, float $radius, string $type): array
    {
        $activeModels = UniverseModel::where('is_archived', false)->get();
        $affectedIds = [];

        foreach ($activeModels as $model) {
            $universe = $this->repository->find($model->id);
            if (!$universe || !$universe->getCoords()) continue;

            $coords = $universe->getCoords();
            $distance = $this->calculateDistance($center, $coords);

            if ($distance <= $radius) {
                $this->applyCrisisEffect($universe, $type, $distance / $radius);
                $affectedIds[] = $universe->getId();
            }
        }

        return $affectedIds;
    }

    private function calculateDistance(array $c1, array $c2): float
    {
        $dx = ($c1['x'] ?? 0) - ($c2['x'] ?? 0);
        $dy = ($c1['y'] ?? 0) - ($c2['y'] ?? 0);
        $dz = ($c1['z'] ?? 0) - ($c2['z'] ?? 0);

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    private function applyCrisisEffect(\WorldOS\Domains\Evolution\ValueObjects\Universe $universe, string $type, float $proximityFactor): void
    {
        $state = $universe->getState();
        $severity = 1.0 - $proximityFactor; // Stronger at center

        $newEntropy = $state->getEntropy();
        $newTrauma = $state->getTrauma();
        $newOrder = $state->getOrder();

        switch ($type) {
            case 'VOID_LEAK':
                $newEntropy += 0.2 * $severity;
                $newTrauma += 0.3 * $severity;
                $newOrder -= 0.1 * $severity;
                break;
            case 'CHRONO_STORM':
                $newEntropy += 0.4 * $severity;
                break;
            case 'ORDER_RESONANCE':
                $newOrder += 0.2 * $severity;
                $newEntropy -= 0.1 * $severity;
                break;
        }

        // Clamp
        $newEntropy = max(0, min(1, $newEntropy));
        $newTrauma = max(0, min(1, $newTrauma));
        $newOrder = max(0, min(1, $newOrder));

        $newState = WorldStateVector::create(
            $newOrder,
            $newEntropy,
            $state->getCohesion(),
            $state->getLegitimacy(),
            $state->getInnovation(),
            $state->getMilitary(),
            $state->getInequality(),
            $newTrauma,
            $state->getEliteCohesion(),
            $state->getResourceStock()
        );

        // We need a way to update the universe state.
        // Currently Universe entity has no setState, but we can return a new one or modify it.
        // Let's assume we can save it via repository.
        
        // Wait, Universe internal state is private. 
        // I should add a method to Universe to apply a Crisis.
        
        // For now, I'll use repository to save a reconstructed one.
        $newUniverse = new \WorldOS\Domains\Evolution\ValueObjects\Universe(
            $newState,
            $universe->getParameters(),
            $universe->getId(),
            $universe->getAge(),
            $universe->getCoords()
        );
        
        $this->repository->save($newUniverse);
    }
}



