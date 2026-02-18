<?php

namespace App\Domains\Material\State;

use Illuminate\Support\Facades\DB;

/**
 * CompressedSnapshotRepository - Optimized snapshot storage with compression
 * 
 * Reduces storage by 70-90% using gzip compression.
 */
class CompressedSnapshotRepository
{
    /**
     * Save compressed snapshot.
     */
    public function saveSnapshot(WorldState $state): void
    {
        $json = json_encode($state->toArray());
        $compressed = gzencode($json, 9); // Max compression level
        
        DB::table('world_state_snapshots')->insert([
            'world_id' => $state->worldId,
            'epoch' => $state->epoch,
            'core_state' => $compressed,
            'structural_state' => null,
            'symbolic_state' => null,
            'memory_state' => null,
            'interaction_state' => null,
            'meta_state' => null,
            'created_at' => now(),
        ]);
    }

    /**
     * Get latest snapshot (decompressed).
     */
    public function getLatestSnapshot(string $worldId, int $maxEpoch): ?WorldState
    {
        $row = DB::table('world_state_snapshots')
            ->where('world_id', $worldId)
            ->where('epoch', '<=', $maxEpoch)
            ->orderBy('epoch', 'desc')
            ->first();

        if (!$row) {
            return null;
        }

        // Decompress
        $json = gzdecode($row->core_state);
        $data = json_decode($json, true);

        return WorldState::fromArray($data);
    }

    /**
     * Prune old snapshots (keep every 10th, 100th, 1000th).
     */
    public function pruneSnapshots(string $worldId): int
    {
        $snapshots = DB::table('world_state_snapshots')
            ->where('world_id', $worldId)
            ->orderBy('epoch', 'asc')
            ->get();

        $toDelete = [];

        foreach ($snapshots as $snapshot) {
            $epoch = $snapshot->epoch;

            // Keep if divisible by 1000, 100, or 10
            if ($epoch % 1000 === 0 || $epoch % 100 === 0 || $epoch % 10 === 0) {
                continue;
            }

            $toDelete[] = $snapshot->id;
        }

        if (empty($toDelete)) {
            return 0;
        }

        return DB::table('world_state_snapshots')
            ->whereIn('id', $toDelete)
            ->delete();
    }

    /**
     * Get compression statistics.
     */
    public function getCompressionStats(string $worldId): array
    {
        $snapshots = DB::table('world_state_snapshots')
            ->where('world_id', $worldId)
            ->get();

        $totalOriginal = 0;
        $totalCompressed = 0;

        foreach ($snapshots as $snapshot) {
            $compressed = strlen($snapshot->core_state);
            $original = strlen(gzdecode($snapshot->core_state));

            $totalOriginal += $original;
            $totalCompressed += $compressed;
        }

        $compressionRatio = $totalOriginal > 0 
            ? (1 - ($totalCompressed / $totalOriginal)) * 100 
            : 0;

        return [
            'total_snapshots' => count($snapshots),
            'original_size' => $totalOriginal,
            'compressed_size' => $totalCompressed,
            'compression_ratio' => round($compressionRatio, 2),
            'space_saved' => $totalOriginal - $totalCompressed,
        ];
    }
}
