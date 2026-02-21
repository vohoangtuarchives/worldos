<?php

namespace Tuzy\Infrastructure\History\Repositories;

use App\Models\Myth;
use App\Models\MythVersion;
use Illuminate\Support\Collection;

class MythEloquentRepository implements MythRepositoryInterface
{
    public function findActiveMythsForWorld(string $worldId): Collection
    {
        return Myth::where('world_id', $worldId)
            ->where('state', 'active')
            ->with(['currentVersion', 'scar'])
            ->get();
    }

    public function findById(string $id): ?Myth
    {
        return Myth::with(['currentVersion', 'versions'])->find($id);
    }

    public function save(Myth $myth): void
    {
        $myth->save();
    }

    public function saveVersion(MythVersion $version): void
    {
        $version->save();
        
        // If this version is newer or explicitly set as current, update the myth?
        // Usually handled by the service layer, but good to have the capability here.
    }
}
