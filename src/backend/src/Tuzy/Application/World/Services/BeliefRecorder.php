<?php

namespace Tuzy\Application\World\Services;

use App\Models\World;
use App\Models\WorldBelief;
use Illuminate\Support\Facades\DB;

class BeliefRecorder
{
    /**
     * Record a belief in the world.
     * Beliefs are repeated thoughts or convictions.
     * If a belief already exists, its intensity and repetition count increase.
     */
    public function record(World $world, string $content): WorldBelief
    {
        $belief = WorldBelief::firstOrNew([
            'world_id' => $world->id,
            'content'  => $content,
        ]);

        if ($belief->exists) {
            $belief->increment('intensity');
            $belief->increment('repeat_count');
        } else {
            $belief->intensity = 1;
            $belief->repeat_count = 1;
            $belief->save();
        }

        return $belief;
    }
}
