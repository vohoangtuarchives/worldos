<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Graph;

/**
 * Calculates graph metrics like Centrality and Civilization Dominance.
 */
class GraphMetrics
{
    /**
     * Calculates Degree Centrality for a subset of nodes (e.g. within a civilization).
     * @param RegionNode[] $nodes The subgraph to analyze
     */
    public function calculateDegreeCentrality(WorldGraph $graph, array $nodes): array
    {
        $centrality = [];
        $totalNodes = count($nodes);
        
        if ($totalNodes <= 1) {
            foreach ($nodes as $node) {
                $centrality[$node->id] = 1.0;
            }
            return $centrality;
        }

        $nodeIds = array_map(fn($n) => $n->id, $nodes);

        foreach ($nodes as $node) {
            $neighbors = $graph->getNeighbors($node->id);
            $internalDegree = 0;
            
            // Count internal connections
            foreach ($neighbors as $edge) {
                if (in_array($edge->nodeB, $nodeIds)) {
                    $internalDegree++;
                }
            }
            
            // Normalized degree centrality
            $centrality[$node->id] = $internalDegree / ($totalNodes - 1);
        }

        return $centrality;
    }

    /**
     * Calculates the aggregate dominance of a civilization.
     * dominance = sum(resource_k * centrality_k)
     *
     * @param string $civilizationId
     * @param WorldGraph $graph
     */
    public function calculateDominance(string $civilizationId, WorldGraph $graph): float
    {
        $subgraph = $graph->getCivilizationSubgraph($civilizationId);
        if (empty($subgraph)) {
            return 0.0;
        }

        $centralities = $this->calculateDegreeCentrality($graph, $subgraph);
        $dominance = 0.0;

        foreach ($subgraph as $node) {
            $c = $centralities[$node->id] ?? 0.0;
            // Base value even for disconnected nodes, but centralized nodes contribute more
            $weight = 0.5 + 0.5 * $c; 
            
            // We use currentResources as the economic base
            $dominance += $node->currentResources * $weight;
        }

        return $dominance;
    }
}
