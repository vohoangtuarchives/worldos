<?php

namespace App\Domains\World\Memory;

use Illuminate\Support\Facades\DB;

class ContradictionMemoryRepository
{
    public function logResolution(string $worldId, string $contradictionId, string $strategy, string $contextHash): ContradictionMemory
    {
        return ContradictionMemory::create([
            'world_id' => $worldId,
            'contradiction_id' => $contradictionId,
            'strategy_used' => $strategy,
            'effectiveness' => null, // Evaluated later
            'context_hash' => $contextHash
        ]);
    }

    public function findSimilarContext(string $worldId, string $contextHash): ?ContradictionMemory
    {
        return ContradictionMemory::where('world_id', $worldId)
            ->where('context_hash', $contextHash)
            ->whereNotNull('effectiveness')
            ->orderByDesc('effectiveness') // Prefer successful strategies
            ->first();
    }

    public function evaluateEffectiveness(int $memoryId, float $score): void
    {
        $memory = ContradictionMemory::find($memoryId);
        if ($memory) {
            $memory->update(['effectiveness' => $score]);
        }
    }
}
