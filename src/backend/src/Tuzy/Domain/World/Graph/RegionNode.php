<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Graph;

use Tuzy\Domain\Math\Vector;

/**
 * Represents a geographical region in the WorldGraph.
 */
class RegionNode
{
    public function __construct(
        public readonly string $id,
        public float $population,
        public float $resourceCapacity,
        public float $currentResources,
        public float $infrastructure,
        public Vector $culturalVector,
        
        // The ID of the Civilization that currently controls this node
        public ?string $controlledByCivId = null
    ) {}

    /**
     * Extracts resources up to the capacity and updates current stock.
     */
    public function extractResources(float $requestedAmount): float
    {
        $extracted = min($this->currentResources, $requestedAmount);
        $this->currentResources -= $extracted;
        return $extracted;
    }

    /**
     * Regenerates resources up to capacity over time.
     */
    public function regenerateResources(float $regenerationRate): void
    {
        $this->currentResources = min(
            $this->resourceCapacity, 
            $this->currentResources + $regenerationRate
        );
    }
}
