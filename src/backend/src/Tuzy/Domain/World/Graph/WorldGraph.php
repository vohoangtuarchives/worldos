<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Graph;

use Tuzy\Domain\Math\Vector;

class WorldGraph
{
    /** @var array<string, RegionNode> */
    private array $nodes = [];

    /** @var array<string, Edge[]> */
    private array $adjacencyList = [];

    public function addNode(RegionNode $node): void
    {
        $this->nodes[$node->id] = $node;
        if (!isset($this->adjacencyList[$node->id])) {
            $this->adjacencyList[$node->id] = [];
        }
    }

    public function addEdge(Edge $edge): void
    {
        // Assuming undirected graph for geography
        $this->adjacencyList[$edge->nodeA][] = $edge;
        
        $reverseEdge = new Edge(
            $edge->nodeB,
            $edge->nodeA,
            $edge->distance,
            $edge->friction,
            $edge->tradeCapacity
        );
        $this->adjacencyList[$edge->nodeB][] = $reverseEdge;
    }

    public function getNode(string $id): ?RegionNode
    {
        return $this->nodes[$id] ?? null;
    }

    /**
     * @return RegionNode[]
     */
    public function getAllNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @return Edge[]
     */
    public function getNeighbors(string $nodeId): array
    {
        return $this->adjacencyList[$nodeId] ?? [];
    }
    
    /**
     * Returns all regions controlled by a specific civilization
     * @return RegionNode[]
     */
    public function getCivilizationSubgraph(string $civilizationId): array
    {
        return array_filter($this->nodes, function (RegionNode $node) use ($civilizationId) {
            return $node->controlledByCivId === $civilizationId;
        });
    }

    /**
     * Laplacian cultural diffusion: C_k(t+1) = C_k(t) + lambda * sum(C_j - C_k)
     * Performs one discrete step of diffusion across the entire world graph.
     */
    public function applyCulturalDiffusion(float $lambda = 0.05): void
    {
        $nextCultureVectors = [];

        foreach ($this->nodes as $nodeId => $node) {
            $neighbors = $this->getNeighbors($nodeId);
            $cumulativeDiff = Vector::zero($node->culturalVector->dimensions());

            foreach ($neighbors as $edge) {
                $neighborNode = $this->getNode($edge->nodeB);
                if (!$neighborNode) continue;

                $diff = $neighborNode->culturalVector->subtract($node->culturalVector);
                // Weight the diffusion by edge conductivity
                $weightedDiff = $diff->scale($edge->getConductivity());
                $cumulativeDiff = $cumulativeDiff->add($weightedDiff);
            }

            // C_k(t+1)
            $nextCultureVectors[$nodeId] = $node->culturalVector->add($cumulativeDiff->scale($lambda));
        }

        // Apply back
        foreach ($nextCultureVectors as $nodeId => $vector) {
            $this->nodes[$nodeId]->culturalVector = $vector;
        }
    }
}
