<?php

namespace App\Domains\Material\Repositories;

use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\Material;
use App\Domains\Material\MaterialInstance;
use Illuminate\Database\Eloquent\Collection;

class MaterialEloquentRepository implements MaterialRepositoryInterface
{
    public function findByCode(string $code): ?Material
    {
        return Material::where('code', $code)->first();
    }

    public function getAll(): Collection
    {
        return Material::all();
    }

    public function create(array $data): Material
    {
        return Material::create($data);
    }

    public function createInstance(Material $material, string $worldId, array $initialState = []): MaterialInstance
    {
        return MaterialInstance::create([
            'material_id' => $material->id,
            'world_id' => $worldId,
            'strength_level' => $initialState['strength_level'] ?? 0,
            'mutation_state' => $initialState['mutation_state'] ?? [],
            'activation_epoch' => $initialState['activation_epoch'] ?? null,
        ]);
    }

    public function getInstancesForWorld(string $worldId): Collection
    {
        return MaterialInstance::where('world_id', $worldId)
            ->with('material')
            ->get();
    }

    public function findInstance(string $instanceId): ?MaterialInstance
    {
        return MaterialInstance::find($instanceId);
    }

    public function updateInstance(MaterialInstance $instance, array $data): MaterialInstance
    {
        // Sanitize data to remove relations and un-updatable fields
        unset($data['material'], $data['world'], $data['id'], $data['created_at'], $data['updated_at']);

        $instance->update($data);
        return $instance;
    }
}
