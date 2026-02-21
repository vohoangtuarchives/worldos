<?php

namespace Tuzy\Application\World\Services;

use Tuzy\Domain\World\Contracts\WorldMetricCalculator;

class BasicWorldMetricCalculator implements WorldMetricCalculator
{
    public function calculate(array $snapshot): array
    {
        $characters = $snapshot['characters'] ?? [];
        $conflicts = $snapshot['conflicts'] ?? [];
        $world = $snapshot['world'] ?? [];

        $totalPower = 0;
        $strongestCharacterId = null;
        $maxPower = -1;

        foreach ($characters as $id => $char) {
            $power = $char['attributes']['power_base'] ?? 0; // Assuming dynamic attributes structure
            $totalPower += $power;

            if ($power > $maxPower) {
                $maxPower = $power;
                $strongestCharacterId = $id;
            }
        }

        return [
            'strongest_character_id' => $strongestCharacterId,
            'total_power' => $totalPower,
            'active_conflict_count' => count($conflicts),
            'myth_density' => $world['myth_density'] ?? 0,
        ];
    }
}
