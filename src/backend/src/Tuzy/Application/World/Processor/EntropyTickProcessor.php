<?php

namespace Tuzy\Application\World\Processor;

use Tuzy\Domain\World\Contracts\TickProcessor;
use Tuzy\Domain\World\Contracts\WorldPreset;
use Tuzy\Application\World\Support\DeterministicRandom;

class EntropyTickProcessor implements TickProcessor
{
    public function process(array $snapshot, WorldPreset $preset, DeterministicRandom $random): array
    {
        // Example logic: Increase global entropy slightly
        if (!isset($snapshot['world']['entropy'])) {
            $snapshot['world']['entropy'] = 0;
        }

        // Logic could be based on preset policies
        // For now, simple increment
        $increase = $random->float(0.001, 0.005);
        $snapshot['world']['entropy'] += $increase;

        return $snapshot;
    }
}
