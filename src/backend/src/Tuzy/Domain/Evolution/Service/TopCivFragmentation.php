<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\World\Graph\WorldGraph;
use Tuzy\Domain\World\Graph\RegionNode;
use Exception;

/**
 * Handles the fragmentation of massive empires into smaller states during an Epoch Reset.
 */
class TopCivFragmentation
{
    /**
     * Fragments a given subgraph into multiple smaller components.
     * A simplified Louvain-like heuristic clustering based on distance, friction, and culture.
     * 
     * @param WorldGraph $graph The full graph
     * @param string $empireId The civilization to partition
     * @param int $targetPieces The number of pieces to shatter into
     * @return array<string, array<string>> Returns associative array of new Civ IDs to list of RegionNode IDs.
     */
    public function fragmentEmpire(WorldGraph $graph, string $empireId, int $targetPieces = 3): array
    {
        $subgraphNodes = $graph->getCivilizationSubgraph($empireId);
        $totalNodes = count($subgraphNodes);
        
        if ($totalNodes < $targetPieces) {
            // Too small to fragment into the target pieces
            $newId = $empireId . '_remnant';
            return [$newId => array_map(fn($n) => $n->id, $subgraphNodes)];
        }

        // K-Medoids / K-Means simplified approach for clustering RegionNodes
        // 1. Pick $targetPieces random seed nodes
        shuffle($subgraphNodes);
        $seeds = array_slice($subgraphNodes, 0, $targetPieces);
        
        $newNations = [];
        foreach ($seeds as $i => $seed) {
            $newNations[$empireId . '_frag_' . $i] = [$seed->id];
        }

        $seedIds = array_map(fn($n) => $n->id, $seeds);
        
        // 2. Assign remaining nodes to nearest seed (based on graph hops / distance approximation)
        foreach ($subgraphNodes as $node) {
            if (in_array($node->id, $seedIds)) {
                continue;
            }

            // Find closest seed based on cultural distance (to represent cultural fracturing)
            // or geographical distance if defined
            $bestSeedIndex = 0;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($seeds as $i => $seed) {
                // Approximate distance using cultural vector mismatch
                $diff = $node->culturalVector->distance($seed->culturalVector);
                if ($diff < $minDistance) {
                    $minDistance = $diff;
                    $bestSeedIndex = $i;
                }
            }

            $newNations[$empireId . '_frag_' . $bestSeedIndex][] = $node->id;
        }

        return $newNations;
    }
}
