<?php

declare(strict_types=1);

namespace App\Infrastructure\Kernel;

use Illuminate\Support\Facades\DB;

/**
 * Persists individual snapshot vectors immutably.
 */
class SnapshotRepository
{
    /**
     * Store a deterministic snapshot hash chain link.
     * MUST be append-only.
     */
    public function storeSnapshot(
        string $experimentId,
        int $tick,
        array $stateVector,
        ?array $inputVector,
        array $structureParams,
        ?array $rngState,
        string $snapshotHash,
        string $previousHash
    ): void {
        DB::table('kernel_experiment_snapshots')->insert([
            'experiment_id' => $experimentId,
            'tick' => $tick,
            'state_vector' => json_encode($stateVector, JSON_PRESERVE_ZERO_FRACTION),
            'input_vector' => $inputVector ? json_encode($inputVector, JSON_PRESERVE_ZERO_FRACTION) : null,
            'structure_params' => json_encode($structureParams, JSON_PRESERVE_ZERO_FRACTION),
            'rng_state' => $rngState ? json_encode($rngState) : null,
            'snapshot_hash' => $snapshotHash,
            'previous_hash' => $previousHash,
            'created_at' => now(),
        ]);
    }
}
