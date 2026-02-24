<?php

namespace WorldOS\World\Application\Services;

use App\Models\World;
use App\Models\WorldBelief;

class BeliefRecorder
{
    public function record(World $world, string $content): WorldBelief
    {
        return WorldBelief::create([
            'world_id' => $world->id,
            'content' => $content,
            'intensity' => 0.1,
            'repeat_count' => 1,
        ]);
    }
}
