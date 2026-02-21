<?php

namespace Tuzy\Domain\Material\Contracts;

use Tuzy\Domain\Material\Material;
use Tuzy\Domain\Material\MaterialInstance;
use Illuminate\Database\Eloquent\Collection;

interface MaterialRepositoryInterface
{
    public function findByCode(string $code): ?Material;
    public function getAll(): Collection;
    public function create(array $data): Material;
    
    // Instance methods
    public function createInstance(Material $material, string $worldId, array $initialState = []): MaterialInstance;
    public function getInstancesForWorld(string $worldId): Collection;
    public function findInstance(string $instanceId): ?MaterialInstance;
    public function updateInstance(MaterialInstance $instance, array $data): MaterialInstance;
}
