<?php

namespace Tuzy\Infrastructure\History\Repositories;

use App\Models\Scar;
use Illuminate\Support\Collection;

class ScarEloquentRepository implements ScarRepositoryInterface
{
    public function findActiveScarsForWorld(string $worldId): Collection
    {
        return Scar::where('world_id', $worldId)
            ->where('state', 'active')
            ->with('counterforces')
            ->get();
    }

    public function findActiveScarsForFaction(string $worldId, string $factionId): Collection
    {
        return Scar::where('world_id', $worldId)
            ->where('state', 'active')
            ->where(function ($query) use ($factionId) {
                $query->where('faction_id', $factionId)
                      ->orWhereNull('faction_id');
            })
            ->with('counterforces')
            ->get();
    }

    public function save(Scar $scar): void
    {
        $scar->save();
    }
}
