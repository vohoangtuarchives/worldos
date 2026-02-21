<?php

namespace Tuzy\Application\World\Processor;

use Tuzy\Domain\World\Contracts\TickProcessor;
use Tuzy\Domain\World\Contracts\WorldPreset;
use Tuzy\Application\World\Support\DeterministicRandom;

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
