<?php

namespace Tuzy\Infrastructure\Cosmology\Repositories;

use Tuzy\Application\Cosmology\Entities\Universe;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use App\Models\UniverseModel;

class CosmologyRepository
{
    /**
     * Return world_id for a universe (for cross-module read without holding Eloquent).
     * world_id is UUID (string).
     */
    public function getWorldIdForUniverse(string $id): ?string
    {
        $v = UniverseModel::where('id', $id)->value('world_id');
        return $v !== null ? (string) $v : null;
    }

    /**
     * WorldOS 2.0: Get runtime state (age, entropy) from first Universe of this world.
     * Use when runtime source of truth should be Universe, not World.current_time/entropy.
     *
     * @return array{universe_id: string, age: int, entropy: float}|null
     */
    public function getRuntimeStateForWorld(string $worldId): ?array
    {
        $model = UniverseModel::where('world_id', $worldId)->whereNull('death_cause')->orderBy('id')->first();
        if ($model === null) {
            return null;
        }
        $state = $model->state_vector ?? [];
        $entropy = (float) ($state['entropy'] ?? 0.5);
        return [
            'universe_id' => $model->id,
            'age' => (int) ($model->age ?? 0),
            'entropy' => $entropy,
        ];
    }

    public function find(string $id): ?Universe
    {
        $model = UniverseModel::find($id);
        
        if (!$model) {
            return null;
        }

        // Reconstruct Universe from DB array data
        $stateData = $model->state_vector ?? [];
        
        // Handle defaults and 10-dimension vector
        $vector = WorldStateVector::create(
            $stateData['order'] ?? 0.5,
            $stateData['entropy'] ?? 0.5,
            $stateData['cohesion'] ?? 0.5,
            $stateData['legitimacy'] ?? 0.5,
            $stateData['innovation'] ?? 0.5,
            $stateData['military'] ?? 0.5,
            $stateData['inequality'] ?? 0.0,
            $stateData['trauma'] ?? 0.0,
            $stateData['elite_cohesion'] ?? 0.5,
            $stateData['resource_stock'] ?? 0.5
        );

        return new Universe($vector, $model->parameters ?? [], $id, (int) ($model->age ?? 0), $model->coords, $model->cosmic_faction_id);
    }

    public function save(Universe $universe, ?string $worldId = null): void
    {
        $state = $universe->getState();

        $payload = [
            'name' => 'Universe ' . substr($universe->getId(), 0, 8),
            'age' => $universe->getAge(),
            'parameters' => $universe->getParameters(),
            'state_vector' => [
                'order' => $state->getOrder(),
                'entropy' => $state->getEntropy(),
                'cohesion' => $state->getCohesion(),
                'legitimacy' => $state->getLegitimacy(),
                'innovation' => $state->getInnovation(),
                'military' => $state->getMilitary(),
                'inequality' => $state->getInequality(),
                'trauma' => $state->getTrauma(),
                'elite_cohesion' => $state->getEliteCohesion(),
                'resource_stock' => $state->getResourceStock(),
            ],
            'coords' => $universe->getCoords(),
            'cosmic_faction_id' => $universe->getCosmicFactionId(),
        ];
        if ($worldId !== null) {
            $payload['world_id'] = $worldId;
        }

        UniverseModel::updateOrCreate(
            ['id' => $universe->getId()],
            $payload
        );
    }
    
    /**
     * Find universe by id. Does not create a new universe (Universe must be created via createCustom with world_id).
     *
     * @throws \InvalidArgumentException when universe does not exist
     */
    public function findOrSeed(string $id): Universe
    {
        $existing = $this->find($id);
        if ($existing) {
            return $existing;
        }

        throw new \InvalidArgumentException('Universe not found. Create via POST /api/cosmology with world_id.');
    }

    public function createCustom(string $id, array $data): Universe
    {
        $worldId = (string) $data['world_id'];
        if (!\App\Models\World::where('id', $worldId)->exists()) {
            throw new \InvalidArgumentException('Universe must belong to an existing World. world_id is required.');
        }

        // 1. Determine Initial State based on "Archetype" or raw vectors
        $archetype = $data['archetype'] ?? 'BALANCED';
        
        $order = 0.5; $entropy = 0.5; $innovation = 0.5;
        
        switch ($archetype) {
            case 'UTOPIAN': $order = 0.9; $entropy = 0.1; break;
            case 'DYSTOPIAN': $order = 0.9; $entropy = 0.4; $innovation = 0.1; break;
            case 'CHAOTIC': $order = 0.2; $entropy = 0.9; break;
            case 'VOID_TOUCHED': $order = 0.1; $entropy = 0.95; break;
        }

        $initialState = WorldStateVector::create(
            $order, $entropy, 0.5, 0.5, $innovation, 0.2,
            0.0, 0.0, 0.5, 0.5
        );

        // 2. Set Parameters (random_seed for deterministic tick/fork)
        $params = [
            'name' => $data['name'] ?? 'Unnamed Universe',
            'designation' => $data['designation'] ?? null,
            'creator' => 'Architect',
            'random_seed' => $data['random_seed'] ?? abs(crc32($id)),
        ];

        // 3. Create Entity
        $universe = new Universe(
            $initialState,
            $params,
            $id,
            0,
            null,
            $data['faction_id'] ?? null
        );

        // 4. Save (world_id required)
        $this->save($universe, $worldId);

        return $universe;
    }
}
