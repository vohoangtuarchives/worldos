<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Graph;

/**
 * Represents a geographical or trade connection between two RegionNodes.
 */
class Edge
{
    public function __construct(
        public readonly string $nodeA,
        public readonly string $nodeB,
        
        // Physical distance between the two regions
        public readonly float $distance,
        
        // Friction reduces diffusion and flow (e.g., mountains, oceans)
        public readonly float $friction = 1.0,
        
        // Base capacity for flow (e.g., roads, river widths)
        public float $tradeCapacity = 1.0
    ) {}

    /**
     * Calculates the effective conductivity for diffusion / trade between the two nodes.
     * Higher conductivity means easier exchange.
     */
    public function getConductivity(): float
    {
        if ($this->friction <= 0 || $this->distance <= 0) {
            return 0.0;
        }
        
        return $this->tradeCapacity / ($this->distance * $this->friction);
    }
}
