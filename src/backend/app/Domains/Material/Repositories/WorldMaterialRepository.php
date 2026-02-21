<?php

declare(strict_types=1);

namespace App\Domains\Material\Repositories;

use App\Domains\Material\ValueObjects\MaterialInstance;
use Tuzy\Domain\Material\ValueObject\MaterialChange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WorldMaterialRepository
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Save material instance
     */
    public function save(MaterialInstance $instance): MaterialInstance
    {
        $data = $this->instanceToArray($instance);
        
        if ($instance->id()) {
            // Update existing instance
            DB::table('world_materials')
                ->where('id', $instance->id())
                ->update($data);
        } else {
            // Insert new instance
            $data['id'] = $this->generateId();
            $data['created_at'] = now();
            $data['updated_at'] = now();
            
            DB::table('world_materials')->insert($data);
            
            // Create new instance with ID
            $instance = new MaterialInstance(
                $data['id'],
                $data['world_id'],
                $data['material_id'],
                $data['location'],
                $data['quantity'],
                $data['quality'],
                $data['properties'],
                $data['owner'],
                $data['activation_epoch'],
                $data['retired_at'],
                new \DateTime($data['created_at']),
                now()
            );
        }

        // Clear cache
        $this->clearCacheForWorld($instance->worldId());

        return $instance;
    }

    /**
     * Find material instance by ID
     */
    public function findInstance(string $worldId, string $instanceId): ?MaterialInstance
    {
        $cacheKey = "material_instance_{$instanceId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId, $instanceId) {
            $data = DB::table('world_materials')
                ->where('world_id', $worldId)
                ->where('id', $instanceId)
                ->first();

            return $data ? $this->arrayToInstance($data) : null;
        });
    }

    /**
     * Find instances by material ID
     */
    public function findInstancesByMaterialId(string $worldId, string $materialId): Collection
    {
        $cacheKey = "material_instances_{$worldId}_{$materialId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId, $materialId) {
            $data = DB::table('world_materials')
                ->where('world_id', $worldId)
                ->where('material_id', $materialId)
                ->whereNull('retired_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToInstance($item));
        });
    }

    /**
     * Get all material instances for world
     */
    public function getInstancesForWorld(string $worldId): Collection
    {
        $cacheKey = "world_materials_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $data = DB::table('world_materials')
                ->where('world_id', $worldId)
                ->whereNull('retired_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToInstance($item));
        });
    }

    /**
     * Record material change
     */
    public function recordChange(string $worldId, MaterialChange $change): void
    {
        $data = [
            'id' => $this->generateId(),
            'world_id' => $worldId,
            'material_instance_id' => $change->instanceId(),
            'change_type' => $change->type(),
            'old_value' => json_encode($change->oldValue()),
            'new_value' => json_encode($change->newValue()),
            'reason' => $change->reason(),
            'epoch' => $change->epoch(),
            'created_at' => now(),
        ];

        DB::table('material_changes')->insert($data);

        // Clear cache
        $this->clearCacheForWorld($worldId);
    }

    /**
     * Get material changes for world
     */
    public function getChangesForWorld(string $worldId, int $limit = 100): Collection
    {
        $cacheKey = "material_changes_{$worldId}_{$limit}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId, $limit) {
            $data = DB::table('material_changes')
                ->where('world_id', $worldId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $data->map(fn($item) => $this->arrayToChange($item));
        });
    }

    /**
     * Get material statistics for world
     */
    public function getStatistics(string $worldId): array
    {
        $cacheKey = "material_stats_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $stats = DB::table('world_materials')
                ->where('world_id', $worldId)
                ->whereNull('retired_at')
                ->selectRaw('
                    COUNT(*) as total_instances,
                    COUNT(DISTINCT material_id) as unique_materials,
                    SUM(quantity) as total_quantity,
                    AVG(quality) as avg_quality,
                    MAX(quality) as max_quality,
                    MIN(quality) as min_quality,
                    COUNT(CASE WHEN owner IS NOT NULL THEN 1 END) as owned_instances,
                    COUNT(DISTINCT location) as unique_locations
                ')
                ->first();

            return [
                'total_instances' => (int) $stats->total_instances,
                'unique_materials' => (int) $stats->unique_materials,
                'total_quantity' => (float) $stats->total_quantity,
                'avg_quality' => (float) $stats->avg_quality,
                'max_quality' => (float) $stats->max_quality,
                'min_quality' => (float) $stats->min_quality,
                'owned_instances' => (int) $stats->owned_instances,
                'unique_locations' => (int) $stats->unique_locations,
            ];
        });
    }

    /**
     * Get materials by location
     */
    public function findByLocation(string $worldId, string $location): Collection
    {
        $cacheKey = "materials_location_{$worldId}_{$location}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId, $location) {
            $data = DB::table('world_materials')
                ->where('world_id', $worldId)
                ->where('location', $location)
                ->whereNull('retired_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToInstance($item));
        });
    }

    /**
     * Get materials by owner
     */
    public function findByOwner(string $worldId, string $owner): Collection
    {
        $cacheKey = "materials_owner_{$worldId}_{$owner}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId, $owner) {
            $data = DB::table('world_materials')
                ->where('world_id', $worldId)
                ->where('owner', $owner)
                ->whereNull('retired_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToInstance($item));
        });
    }

    /**
     * Get retired instances
     */
    public function getRetiredInstances(string $worldId): Collection
    {
        $cacheKey = "retired_materials_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $data = DB::table('world_materials')
                ->where('world_id', $worldId)
                ->whereNotNull('retired_at')
                ->orderBy('retired_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToInstance($item));
        });
    }

    /**
     * Check if instance exists
     */
    public function exists(string $instanceId): bool
    {
        return DB::table('world_materials')
            ->where('id', $instanceId)
            ->exists();
    }

    /**
     * Delete instance
     */
    public function delete(string $worldId, string $instanceId): bool
    {
        $deleted = DB::table('world_materials')
            ->where('world_id', $worldId)
            ->where('id', $instanceId)
            ->delete();

        if ($deleted) {
            Cache::forget("material_instance_{$instanceId}");
            $this->clearCacheForWorld($worldId);
        }

        return $deleted > 0;
    }

    private function instanceToArray(MaterialInstance $instance): array
    {
        return [
            'id' => $instance->id(),
            'world_id' => $instance->worldId(),
            'material_id' => $instance->materialId(),
            'location' => $instance->location(),
            'quantity' => $instance->quantity(),
            'quality' => $instance->quality(),
            'properties' => json_encode($instance->properties()),
            'owner' => $instance->owner(),
            'activation_epoch' => $instance->activationEpoch(),
            'retired_at' => $instance->retiredAt(),
            'created_at' => $instance->createdAt(),
            'updated_at' => now(),
        ];
    }

    private function arrayToInstance(object $data): MaterialInstance
    {
        $properties = json_decode($data->properties ?? '{}', true);
        
        return new MaterialInstance(
            $data->id,
            $data->world_id,
            $data->material_id,
            $data->location,
            $data->quantity,
            $data->quality,
            $properties,
            $data->owner,
            $data->activation_epoch,
            $data->retired_at,
            new \DateTime($data->created_at),
            new \DateTime($data->updated_at)
        );
    }

    private function arrayToChange(object $data): MaterialChange
    {
        $oldValue = json_decode($data->old_value ?? '{}', true);
        $newValue = json_decode($data->new_value ?? '{}', true);
        
        return new MaterialChange(
            materialInstanceId: (string) $data->material_instance_id,
            changeType: (string) $data->change_type,
            oldValue: $oldValue,
            newValue: $newValue,
            reason: $data->reason,
            epoch: isset($data->epoch) ? (int) $data->epoch : null,
            createdAt: new \DateTime($data->created_at)
        );
    }

    private function generateId(): string
    {
        return 'mat_inst_' . uniqid() . '_' . time();
    }

    private function clearCacheForWorld(string $worldId): void
    {
        Cache::forget("world_materials_{$worldId}");
        Cache::forget("material_stats_{$worldId}");
        Cache::forget("material_changes_{$worldId}_100");
        Cache::forget("retired_materials_{$worldId}");
    }
}
