<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service\MetaCosmos;

use App\Models\MetaCycle;
use App\Models\Universe as UniverseModel;
use Illuminate\Support\Str;

class MetaCycleManager
{
    public function createCycle(string $worldId, int $generation, array $universeIds): MetaCycle
    {
        return MetaCycle::create([
            'id' => Str::uuid()->toString(),
            'world_id' => $worldId,
            'current_generation' => $generation,
            'status' => 'SIMULATING',
            'payload' => [
                'pending_universes' => $universeIds,
                'completed_universes' => [],
                'results' => []
            ]
        ]);
    }

    public function markUniverseComplete(string $universeId, float $fitness): void
    {
        $model = UniverseModel::find($universeId);
        if (!$model) return;

        $model->update(['fitness' => $fitness]);

        // Find the active meta cycle
        $cycle = MetaCycle::where('world_id', $model->world_id)
            ->where('status', 'SIMULATING')
            ->first();

        if (!$cycle) return;

        $payload = $cycle->payload;
        $payload['completed_universes'][] = $universeId;
        $payload['results'][$universeId] = $fitness;
        
        // Remove from pending
        $payload['pending_universes'] = array_values(array_filter(
            $payload['pending_universes'],
            fn($id) => $id !== $universeId
        ));

        $cycle->update(['payload' => $payload]);

        if (empty($payload['pending_universes'])) {
            $cycle->update(['status' => 'AGGREGATING']);
            // Trigger the next phase of Orchestrator
        }
    }
}
