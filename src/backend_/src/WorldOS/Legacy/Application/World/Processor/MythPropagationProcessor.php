<?php

namespace WorldOS\Legacy\Application\World\Processor;

use WorldOS\Blueprint\Domain\Legacy\Contracts\TickProcessor;
use WorldOS\Blueprint\Domain\Legacy\Contracts\WorldPreset;
use WorldOS\Legacy\Application\World\Support\DeterministicRandom;

class MythPropagationProcessor implements TickProcessor
{
    public function process(array $snapshot, WorldPreset $preset, DeterministicRandom $random): array
    {
        $myths = $snapshot['myths'] ?? [];

        foreach ($myths as $key => &$myth) {
            // Chance to spread
            if ($random->chance(0.1)) {
                $myth['spread_level'] = ($myth['spread_level'] ?? 0) + 1;
                $myth['belief_strength'] = ($myth['belief_strength'] ?? 0) + $random->float(0.01, 0.05);
            }
        }
        
        $snapshot['myths'] = $myths;

        return $snapshot;
    }
}
