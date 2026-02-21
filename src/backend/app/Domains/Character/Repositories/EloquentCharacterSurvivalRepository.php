<?php

declare(strict_types=1);

namespace App\Domains\Character\Repositories;

use App\Domains\Character\Aggregates\CharacterSurvivalAggregate;
use Tuzy\Domain\Character\ValueObject\SurvivalProbability;
use Tuzy\Domain\Character\ValueObject\RiskFactors;
use Tuzy\Domain\Character\ValueObject\NarrativeWeight;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EloquentCharacterSurvivalRepository implements CharacterSurvivalRepository
{
    private const CACHE_TTL = 300; // 5 minutes

    public function save(CharacterSurvivalAggregate $character): CharacterSurvivalAggregate
    {
        $data = $this->characterToArray($character);
        
        if ($character->id()) {
            // Update existing character
            DB::table('character_survival')
                ->where('id', $character->id())
                ->update($data);
        } else {
            // Insert new character
            $data['id'] = $this->generateId();
            $data['created_at'] = now();
            $data['updated_at'] = now();
            
            DB::table('character_survival')->insert($data);
            
            // Create new character with ID
            $character = new CharacterSurvivalAggregate(
                $data['id'],
                $character->worldId(),
                $character->name(),
                $character->faction(),
                $character->location(),
                $character->survivalProbability(),
                $character->riskFactors(),
                $character->narrativeWeight(),
                $character->isAlive(),
                $character->age(),
                $character->causeOfDeath(),
                $character->createdAt(),
                now()
            );
        }

        // Clear cache
        $this->clearCacheForWorld($character->worldId());

        return $character;
    }

    public function findById(string $id): ?CharacterSurvivalAggregate
    {
        $cacheKey = "character_{$id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $data = DB::table('character_survival')
                ->where('id', $id)
                ->first();

            return $data ? $this->arrayToCharacter($data) : null;
        });
    }

    public function findByWorldId(string $worldId): Collection
    {
        $cacheKey = "characters_world_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $data = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToCharacter($item));
        });
    }

    public function findAliveByWorldId(string $worldId): Collection
    {
        $cacheKey = "characters_alive_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $data = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->where('is_alive', true)
                ->orderBy('narrative_weight', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToCharacter($item));
        });
    }

    public function findDeadByWorldId(string $worldId): Collection
    {
        $cacheKey = "characters_dead_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $data = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->where('is_alive', false)
                ->orderBy('death_at', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToCharacter($item));
        });
    }

    public function findByFaction(string $faction, string $worldId = null): Collection
    {
        $query = DB::table('character_survival')
            ->where('faction', $faction);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('narrative_weight', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function findByLocation(string $location, string $worldId = null): Collection
    {
        $query = DB::table('character_survival')
            ->where('location', $location);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('narrative_weight', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function findByNarrativeWeight(float $minWeight, float $maxWeight = null, string $worldId = null): Collection
    {
        $query = DB::table('character_survival')
            ->where('narrative_weight', '>=', $minWeight);

        if ($maxWeight !== null) {
            $query->where('narrative_weight', '<=', $maxWeight);
        }

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('narrative_weight', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function findAtRisk(string $worldId, float $riskThreshold = 0.7): Collection
    {
        $cacheKey = "characters_at_risk_{$worldId}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId, $riskThreshold) {
            $data = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->where('is_alive', true)
                ->whereRaw('(1 - survival_probability) >= ?', [$riskThreshold])
                ->orderBy('survival_probability', 'asc')
                ->get();

            return $data->map(fn($item) => $this->arrayToCharacter($item));
        });
    }

    public function findHighSurvival(string $worldId, float $survivalThreshold = 0.8): Collection
    {
        $cacheKey = "characters_high_survival_{$worldId}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId, $survivalThreshold) {
            $data = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->where('is_alive', true)
                ->where('survival_probability', '>=', $survivalThreshold)
                ->orderBy('survival_probability', 'desc')
                ->get();

            return $data->map(fn($item) => $this->arrayToCharacter($item));
        });
    }

    public function getSurvivalStatistics(string $worldId): array
    {
        $cacheKey = "survival_stats_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $stats = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->selectRaw('
                    COUNT(*) as total_characters,
                    COUNT(CASE WHEN is_alive = true THEN 1 END) as alive_characters,
                    COUNT(CASE WHEN is_alive = false THEN 1 END) as dead_characters,
                    AVG(survival_probability) as avg_survival_probability,
                    AVG(narrative_weight) as avg_narrative_weight,
                    AVG(age) as avg_age,
                    MAX(age) as max_age,
                    MIN(age) as min_age,
                    COUNT(CASE WHEN survival_probability > 0.8 THEN 1 END) as high_survival,
                    COUNT(CASE WHEN survival_probability < 0.3 THEN 1 END) as low_survival,
                    COUNT(CASE WHEN narrative_weight > 0.8 THEN 1 END) as main_characters,
                    COUNT(CASE WHEN narrative_weight < 0.3 THEN 1 END) as minor_characters
                ')
                ->first();

            return [
                'total_characters' => (int) $stats->total_characters,
                'alive_characters' => (int) $stats->alive_characters,
                'dead_characters' => (int) $stats->dead_characters,
                'avg_survival_probability' => (float) $stats->avg_survival_probability,
                'avg_narrative_weight' => (float) $stats->avg_narrative_weight,
                'avg_age' => (float) $stats->avg_age,
                'max_age' => (int) $stats->max_age,
                'min_age' => (int) $stats->min_age,
                'high_survival' => (int) $stats->high_survival,
                'low_survival' => (int) $stats->low_survival,
                'main_characters' => (int) $stats->main_characters,
                'minor_characters' => (int) $stats->minor_characters,
                'survival_rate' => $stats->total_characters > 0 ? 
                    ((int) $stats->alive_characters / (int) $stats->total_characters) * 100 : 0,
            ];
        });
    }

    public function getSurvivalDistribution(string $worldId): array
    {
        $cacheKey = "survival_distribution_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $distribution = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->selectRaw('
                    CASE 
                        WHEN survival_probability >= 0.9 THEN "very_high"
                        WHEN survival_probability >= 0.7 THEN "high"
                        WHEN survival_probability >= 0.5 THEN "medium"
                        WHEN survival_probability >= 0.3 THEN "low"
                        ELSE "very_low"
                    END as survival_range,
                    COUNT(*) as count
                ')
                ->groupBy('survival_range')
                ->orderBy('survival_range', 'desc')
                ->get();

            return $distribution->mapWithKeys(fn($item) => [$item->survival_range => (int) $item->count])->toArray();
        });
    }

    public function getFactionStatistics(string $worldId): array
    {
        $cacheKey = "faction_stats_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $stats = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->selectRaw('
                    faction,
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_alive = true THEN 1 END) as alive,
                    COUNT(CASE WHEN is_alive = false THEN 1 END) as dead,
                    AVG(survival_probability) as avg_survival,
                    AVG(narrative_weight) as avg_weight
                ')
                ->groupBy('faction')
                ->orderBy('alive', 'desc')
                ->get();

            return $stats->map(fn($item) => [
                'faction' => $item->faction,
                'total' => (int) $item->total,
                'alive' => (int) $item->alive,
                'dead' => (int) $item->dead,
                'avg_survival' => (float) $item->avg_survival,
                'avg_weight' => (float) $item->avg_weight,
                'survival_rate' => $item->total > 0 ? ((int) $item->alive / (int) $item->total) * 100 : 0,
            ])->toArray();
        });
    }

    public function getLocationStatistics(string $worldId): array
    {
        $cacheKey = "location_stats_{$worldId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($worldId) {
            $stats = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->selectRaw('
                    location,
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_alive = true THEN 1 END) as alive,
                    COUNT(CASE WHEN is_alive = false THEN 1 END) as dead,
                    AVG(survival_probability) as avg_survival
                ')
                ->groupBy('location')
                ->orderBy('alive', 'desc')
                ->get();

            return $stats->map(fn($item) => [
                'location' => $item->location,
                'total' => (int) $item->total,
                'alive' => (int) $item->alive,
                'dead' => (int) $item->dead,
                'avg_survival' => (float) $item->avg_survival,
                'survival_rate' => $item->total > 0 ? ((int) $item->alive / (int) $item->total) * 100 : 0,
            ])->toArray();
        });
    }

    public function updateSurvivalStatus(string $characterId, bool $isAlive, string $causeOfDeath = null): bool
    {
        $updateData = [
            'is_alive' => $isAlive,
            'updated_at' => now(),
        ];

        if (!$isAlive) {
            $updateData['death_at'] = now();
            $updateData['cause_of_death'] = $causeOfDeath;
            $updateData['survival_probability'] = 0.0;
        } else {
            $updateData['death_at'] = null;
            $updateData['cause_of_death'] = null;
        }

        $updated = DB::table('character_survival')
            ->where('id', $characterId)
            ->update($updateData);

        if ($updated) {
            Cache::forget("character_{$characterId}");
            // Get world ID to clear world cache
            $character = $this->findById($characterId);
            if ($character) {
                $this->clearCacheForWorld($character->worldId());
            }
        }

        return $updated > 0;
    }

    public function updateSurvivalProbability(string $characterId, float $probability): bool
    {
        $updated = DB::table('character_survival')
            ->where('id', $characterId)
            ->update([
                'survival_probability' => $probability,
                'updated_at' => now(),
            ]);

        if ($updated) {
            Cache::forget("character_{$characterId}");
            $character = $this->findById($characterId);
            if ($character) {
                $this->clearCacheForWorld($character->worldId());
            }
        }

        return $updated > 0;
    }

    public function updateNarrativeWeight(string $characterId, float $weight): bool
    {
        $updated = DB::table('character_survival')
            ->where('id', $characterId)
            ->update([
                'narrative_weight' => $weight,
                'updated_at' => now(),
            ]);

        if ($updated) {
            Cache::forget("character_{$characterId}");
            $character = $this->findById($characterId);
            if ($character) {
                $this->clearCacheForWorld($character->worldId());
            }
        }

        return $updated > 0;
    }

    public function delete(string $id): bool
    {
        $character = $this->findById($id);
        
        if (!$character) {
            return false;
        }

        $deleted = DB::table('character_survival')
            ->where('id', $id)
            ->delete();

        if ($deleted) {
            Cache::forget("character_{$id}");
            $this->clearCacheForWorld($character->worldId());
        }

        return $deleted > 0;
    }

    public function exists(string $id): bool
    {
        return DB::table('character_survival')
            ->where('id', $id)
            ->exists();
    }

    public function countByWorld(string $worldId): int
    {
        return DB::table('character_survival')
            ->where('world_id', $worldId)
            ->count();
    }

    public function countAliveByWorld(string $worldId): int
    {
        return DB::table('character_survival')
            ->where('world_id', $worldId)
            ->where('is_alive', true)
            ->count();
    }

    public function countDeadByWorld(string $worldId): int
    {
        return DB::table('character_survival')
            ->where('world_id', $worldId)
            ->where('is_alive', false)
            ->count();
    }

    public function paginate(string $worldId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $data = DB::table('character_survival')
            ->where('world_id', $worldId)
            ->orderBy('narrative_weight', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $total = DB::table('character_survival')
            ->where('world_id', $worldId)
            ->count();

        return [
            'data' => $data->map(fn($item) => $this->arrayToCharacter($item)),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }

    public function getForAnalysis(string $worldId, \DateTime $since = null): Collection
    {
        $query = DB::table('character_survival')
            ->where('world_id', $worldId);

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        return $query->orderBy('created_at', 'desc')->get()
            ->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function getRecentDeaths(string $worldId, int $limit = 10): Collection
    {
        $cacheKey = "recent_deaths_{$worldId}_{$limit}";
        
        return Cache::remember($cacheKey, 60, function () use ($worldId, $limit) {
            $data = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->where('is_alive', false)
                ->whereNotNull('death_at')
                ->orderBy('death_at', 'desc')
                ->limit($limit)
                ->get();

            return $data->map(fn($item) => $this->arrayToCharacter($item));
        });
    }

    public function getSurvivalTrends(string $worldId, int $days = 30): array
    {
        $cacheKey = "survival_trends_{$worldId}_{$days}";
        
        return Cache::remember($cacheKey, 300, function () use ($worldId, $days) {
            $cutoff = Carbon::now()->subDays($days);
            
            $trends = DB::table('character_survival')
                ->where('world_id', $worldId)
                ->where('created_at', '>=', $cutoff)
                ->selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as total_created,
                    COUNT(CASE WHEN is_alive = false THEN 1 END) as total_deaths,
                    AVG(survival_probability) as avg_survival
                ')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            return $trends->map(fn($item) => [
                'date' => $item->date,
                'total_created' => (int) $item->total_created,
                'total_deaths' => (int) $item->total_deaths,
                'avg_survival' => (float) $item->avg_survival,
                'death_rate' => $item->total_created > 0 ? 
                    ((int) $item->total_deaths / (int) $item->total_created) * 100 : 0,
            ])->toArray();
        });
    }

    public function search(string $query, string $worldId = null): Collection
    {
        $dbQuery = DB::table('character_survival')
            ->where('name', 'LIKE', "%{$query}%");

        if ($worldId) {
            $dbQuery->where('world_id', $worldId);
        }

        $data = $dbQuery->orderBy('narrative_weight', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function findByAge(int $minAge, int $maxAge = null, string $worldId = null): Collection
    {
        $query = DB::table('character_survival')
            ->where('age', '>=', $minAge);

        if ($maxAge !== null) {
            $query->where('age', '<=', $maxAge);
        }

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('age', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function findByRiskFactors(array $riskFactors, string $worldId = null): Collection
    {
        // This would require parsing JSON risk factors and matching
        $query = DB::table('character_survival');

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        // For now, return characters with low survival probability
        $query->where('survival_probability', '<', 0.5);

        $data = $query->orderBy('survival_probability', 'asc')->get();

        return $data->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function bulkUpdate(Collection $characters): bool
    {
        $data = $characters->map(fn($character) => $this->characterToArray($character))->toArray();
        
        if (empty($data)) {
            return true;
        }

        $success = DB::transaction(function () use ($data) {
            foreach ($data as $characterData) {
                DB::table('character_survival')
                    ->where('id', $characterData['id'])
                    ->update($characterData);
            }
            return true;
        });

        if ($success) {
            // Clear cache for affected worlds
            $worldIds = $characters->map(fn($c) => $c->worldId())->unique();
            foreach ($worldIds as $worldId) {
                $this->clearCacheForWorld($worldId);
            }
        }

        return $success;
    }

    public function archiveOldDeaths(\DateTime $cutoff, string $worldId = null): int
    {
        $query = DB::table('character_survival')
            ->where('is_alive', false)
            ->where('death_at', '<', $cutoff);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $archived = $query->update(['archived' => true]);

        if ($archived > 0) {
            $this->clearCacheForWorld($worldId);
        }

        return $archived;
    }

    public function findArchived(string $worldId = null): Collection
    {
        $query = DB::table('character_survival')
            ->where('archived', true);

        if ($worldId) {
            $query->where('world_id', $worldId);
        }

        $data = $query->orderBy('archived_at', 'desc')->get();

        return $data->map(fn($item) => $this->arrayToCharacter($item));
    }

    public function restore(string $characterId): bool
    {
        $restored = DB::table('character_survival')
            ->where('id', $characterId)
            ->update(['archived' => false]);

        if ($restored) {
            Cache::forget("character_{$characterId}");
            $character = $this->findById($characterId);
            if ($character) {
                $this->clearCacheForWorld($character->worldId());
            }
        }

        return $restored > 0;
    }

    private function characterToArray(CharacterSurvivalAggregate $character): array
    {
        return [
            'id' => $character->id(),
            'world_id' => $character->worldId(),
            'name' => $character->name(),
            'faction' => $character->faction(),
            'location' => $character->location(),
            'survival_probability' => $character->survivalProbability()->value(),
            'risk_factors' => json_encode($character->riskFactors()->toArray()),
            'narrative_weight' => $character->narrativeWeight()->protectionFactor(),
            'narrative_weight_data' => json_encode($character->narrativeWeight()->toArray()),
            'is_alive' => $character->isAlive(),
            'age' => $character->age(),
            'cause_of_death' => $character->causeOfDeath(),
            'death_at' => $character->isAlive() ? null : now(),
            'created_at' => $character->createdAt(),
            'updated_at' => now(),
        ];
    }

    private function arrayToCharacter(object $data): CharacterSurvivalAggregate
    {
        $riskFactorsData = json_decode($data->risk_factors ?? '{}', true);
        $nwData = json_decode($data->narrative_weight_data ?? '{}', true);
        
        return new CharacterSurvivalAggregate(
            $data->id,
            $data->world_id,
            $data->name,
            $data->faction,
            $data->location,
            new SurvivalProbability((float) $data->survival_probability),
            new RiskFactors($riskFactorsData),
            NarrativeWeight::fromArray($nwData),
            (bool) $data->is_alive,
            (int) $data->age,
            $data->cause_of_death,
            new \DateTime($data->created_at),
            new \DateTime($data->updated_at)
        );
    }

    private function generateId(): string
    {
        return 'char_' . uniqid() . '_' . time();
    }

    private function clearCacheForWorld(?string $worldId): void
    {
        if ($worldId) {
            Cache::forget("characters_world_{$worldId}");
            Cache::forget("characters_alive_{$worldId}");
            Cache::forget("characters_dead_{$worldId}");
            Cache::forget("survival_stats_{$worldId}");
            Cache::forget("survival_distribution_{$worldId}");
            Cache::forget("faction_stats_{$worldId}");
            Cache::forget("location_stats_{$worldId}");
            Cache::forget("characters_at_risk_{$worldId}");
            Cache::forget("characters_high_survival_{$worldId}");
        }
    }
}
