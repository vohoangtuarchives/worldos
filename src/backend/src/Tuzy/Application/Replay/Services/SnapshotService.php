<?php

namespace Tuzy\Application\Replay\Services;

use App\Models\World;
use App\Models\MetaLayerState;
use App\Models\WorldSnapshotV2;
use App\Models\MetaSnapshot;
use Tuzy\Domain\Meta\Aggregates\MetaLayer;
use Illuminate\Support\Str;

class SnapshotService
{
    // Toggle for debug vs production performance
    private bool $enableHashing = true;

    public function captureWorld(World $world, string $simulationRunId): WorldSnapshotV2
    {
        $payload = [
            'ideology' => $world->law_profile, // Assuming simple structure or need transformation
            'physics' => $world->physics_profile,
            'flux' => $world->cosmic_energy,
        ];

        // Hash Calculation for Determinism Check
        $hashString = $world->id . 
            $world->tick . 
            $world->entropy . 
            $world->status .
            json_encode($payload);
            
        $hash = $this->enableHashing ? hash('sha256', $hashString) : 'skipped';

        return WorldSnapshotV2::create([
            'simulation_run_id' => $simulationRunId,
            'world_id' => $world->id,
            'tick' => $world->tick,
            'generation' => $world->generation ?? 0,
            'archetype_id' => $world->archetype_id,
            'status' => $world->status ?? 'unknown',
            'entropy' => $world->entropy ?? 0.0,
            'survival_score' => $world->survival_score ?? 0.0, // Need to implement scoring logic
            'is_prophet' => $world->is_prophet ?? false,
            'state_payload' => $payload,
            'state_hash' => $hash,
        ]);
    }

    public function captureMeta(MetaLayer $metaLayer, string $simulationRunId, int $tick): MetaSnapshot
    {
        $payload = $metaLayer->exportState();
        
        $hashString = $tick . 
            $metaLayer->currentEraIndex . 
            json_encode($metaLayer->ideologyVector) . 
            $metaLayer->chaosPool;

        $hash = $this->enableHashing ? hash('sha256', $hashString) : 'skipped';

        return MetaSnapshot::create([
            'id' => (string) Str::uuid(),
            'simulation_run_id' => $simulationRunId,
            'tick' => $tick,
            'current_era_index' => $metaLayer->currentEraIndex,
            'extinction_threshold' => 150.0, // TODO: Get from Policy or State
            'drift_velocity' => $metaLayer->resourceFlux, // Proxy
            'ideology_vector' => $metaLayer->ideologyVector,
            'sacred_state' => ['sacred_count' => 0], // Placeholder
            'meta_hash' => $hash,
        ]);
    }
}
