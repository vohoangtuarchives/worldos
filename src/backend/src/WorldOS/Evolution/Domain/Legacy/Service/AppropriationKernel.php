<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Blueprint\Domain\Legacy\Graph\WorldGraph;
use WorldOS\Blueprint\Domain\Legacy\Graph\RegionNode;
use WorldOS\Evolution\Domain\Legacy\Entity\CivilizationState;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;

/**
 * Replaces event-based warfare with continuous border resource extraction.
 */
class AppropriationKernel
{
    private float $appropriationRate;

    public function __construct(float $appropriationRate = 0.05)
    {
        $this->appropriationRate = $appropriationRate;
    }

    /**
     * Executes one tick of continuous appropriation across the WorldGraph.
     * Extracts resources from weaker adjacent civilizations based on military disparity.
     *
     * @param WorldGraph $graph
     * @param array<string, CivilizationSnapshot> $civilizationSnapshots Indexed by Civ ID
     */
    public function applyAppropriation(WorldGraph $graph, array $civilizationSnapshots): void
    {
        $nodes = $graph->getAllNodes();
        $transfers = [];

        foreach ($nodes as $nodeId => $node) {
            $transfers[$nodeId] = 0.0; // Net flow into this node
        }

        foreach ($nodes as $nodeId => $node) {
            $civA_Id = $node->controlledByCivId;
            if (!$civA_Id || !isset($civilizationSnapshots[$civA_Id])) {
                continue;
            }

            $civA_military = $civilizationSnapshots[$civA_Id]->militaryPressure;

            $neighbors = $graph->getNeighbors($nodeId);
            foreach ($neighbors as $edge) {
                // To avoid double-counting, only process if nodeA < nodeB lexicographically
                if ($edge->nodeA >= $edge->nodeB) {
                    continue;
                }

                $neighborNode = $graph->getNode($edge->nodeB);
                if (!$neighborNode) continue;

                $civB_Id = $neighborNode->controlledByCivId;
                if (!$civB_Id || !isset($civilizationSnapshots[$civB_Id]) || $civA_Id === $civB_Id) {
                    // No appropriation within the same civilization or against empty land
                    continue;
                }

                $civB_military = $civilizationSnapshots[$civB_Id]->militaryPressure;

                // Military disparity: bounded roughly between -1 and 1
                $disparity = ($civA_military - $civB_military) / (1.0 + abs($civA_military - $civB_military));

                // A_ij = Beta * disparity * Conductivity
                $baseFlow = $this->appropriationRate * $disparity * $edge->getConductivity();

                // If disparity > 0, A takes from B. If disparity < 0, B takes from A.
                if ($baseFlow > 0) {
                    // A extracts from B
                    $extractable = min($neighborNode->currentResources, $baseFlow * $neighborNode->currentResources);
                    $transfers[$edge->nodeB] -= $extractable;
                    $transfers[$edge->nodeA] += $extractable;
                } elseif ($baseFlow < 0) {
                    // B extracts from A
                    $baseFlowAbs = abs($baseFlow);
                    $extractable = min($node->currentResources, $baseFlowAbs * $node->currentResources);
                    $transfers[$edge->nodeA] -= $extractable;
                    $transfers[$edge->nodeB] += $extractable;
                }
            }
        }

        // Apply net transfers
        foreach ($transfers as $nodeId => $netFlow) {
            $node = $graph->getNode($nodeId);
            if ($node) {
                $node->currentResources = max(0.0, min($node->resourceCapacity, $node->currentResources + $netFlow));
            }
        }
    }
}
