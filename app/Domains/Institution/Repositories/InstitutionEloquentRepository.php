<?php

namespace App\Domains\Institution\Repositories;

use App\Models\Institution;
use Illuminate\Support\Collection;

class InstitutionEloquentRepository implements InstitutionRepositoryInterface
{
    public function findAllForWorld(string $worldId): Collection
    {
        return Institution::where('world_id', $worldId)
            ->with(['actions', 'healingEvents'])
            ->get();
    }

    public function findById(string $id): ?Institution
    {
        return Institution::with(['actions', 'healingEvents'])->find($id);
    }

    public function save(Institution $institution): void
    {
        $institution->save();
    }
}
