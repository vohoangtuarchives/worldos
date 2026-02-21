<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\World\Graph\WorldGraph;
use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\World\Graph\RegionNode;

/**
 * Handles the regeneration and depletion cycles of planetary resources.
 */
class ResourceRegenerationService
{
    /**
     * @param WorldGraph $graph
     * @param float $baseRegeneration Regeneration rate assuming perfect conditions
     */
    public function regenerate(WorldGraph $graph, float $baseRegeneration = 0.02): float
    {
        $globalCapacity = 0.0;
        $globalCurrent = 0.0;

        foreach ($graph->getAllNodes() as $node) {
            $node->regenerateResources($baseRegeneration * $node->resourceCapacity);
            
            $globalCapacity += $node->resourceCapacity;
            $globalCurrent += $node->currentResources;
        }

        // Scarcity factor: 1.0 means full resources, 0.0 means completely depleted
        return $globalCapacity > 0 ? ($globalCurrent / $globalCapacity) : 0.0;
    }

    /**
     * Calculate structural penalties for a civilization keeping high military pressure.
     * @return array [ 'population_decay' => float, 'inequality_increase' => float ]
     */
    public function calculateMilitaryPenalty(CivilizationSnapshot $snapshot): array
    {
        $mp = $snapshot->militaryPressure;
        
        // If MP is greater than 0.3, it starts to incur population costs and inequality
        $excess = max(0.0, $mp - 0.3);

        return [
            // Up to 5% population decay per tick if MP is maximized
            'population_decay' => $excess * 0.05, 
            
            // Inequality inherently grows when military demands resources
            'inequality_increase' => $excess * 0.1
        ];
    }
}
