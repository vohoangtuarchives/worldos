<?php

namespace WorldOS\Legacy\Infrastructure\Saga\Repositories;

use WorldOS\Saga\Domain\Legacy\Saga;
use WorldOS\Saga\Domain\Legacy\SagaWorld;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SagaRepository
{
    public function getAllWithStatus(): Collection
    {
        return Saga::withCount('sagaWorlds')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function findById(string $id): Saga
    {
        return Saga::findOrFail($id);
    }

    public function getSagaWorlds(string $sagaId): Collection
    {
        return SagaWorld::where('saga_id', $sagaId)
            ->with(['world', 'universe'])
            ->orderBy('sequence')
            ->get();
    }

    public function getGlobalStats(): array
    {
        return [
            'total_sagas' => Saga::count(),
            'active_sagas' => Saga::where('status', Saga::STATUS_RUNNING)->count(),
            'total_worlds' => SagaWorld::count(),
            'total_chapters' => DB::table('chapter_telemetry')->count(),
        ];
    }
}
