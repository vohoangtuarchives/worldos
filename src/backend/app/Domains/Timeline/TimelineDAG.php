<?php

namespace App\Domains\Timeline;

use InvalidArgumentException;

/**
 * TimelineDAG - Directed Acyclic Graph for Timeline Management
 * 
 * Manages causal and temporal relationships between events.
 */
class TimelineDAG
{
    private array $nodes = [];
    private array $edges = [];

    /**
     * Add a node to the DAG.
     * 
     * @param array $data Node data
     * @return string Node ID
     */
    public function addNode(array $data): string
    {
        $id = uniqid('node_', true);
        
        $this->nodes[$id] = array_merge([
            'id' => $id,
            'created_at' => now(),
        ], $data);
        
        return $id;
    }

    /**
     * Add a directed edge between two nodes.
     * 
     * @param string $fromId Source node ID
     * @param string $toId Target node ID
     * @param string $type Edge type (causal, temporal, etc.)
     * @throws InvalidArgumentException If nodes don't exist or cycle detected
     */
    public function addEdge(string $fromId, string $toId, string $type = 'default'): void
    {
        if (!isset($this->nodes[$fromId]) || !isset($this->nodes[$toId])) {
            throw new InvalidArgumentException("Nodes do not exist: $fromId -> $toId");
        }

        if ($this->detectCycle($fromId, $toId)) {
            throw new InvalidArgumentException("Cycle detected: $fromId -> $toId");
        }

        $this->edges[] = [
            'from' => $fromId,
            'to' => $toId,
            'type' => $type,
        ];
    }

    /**
     * Get all nodes.
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Get all edges.
     */
    public function getEdges(): array
    {
        return $this->edges;
    }

    /**
     * Detect if adding an edge would create a cycle.
     * DFS based cycle detection.
     */
    private function detectCycle(string $fromId, string $toId): bool
    {
        if ($fromId === $toId) {
            return true;
        }

        $visited = [];
        $stack = [$toId];

        while (!empty($stack)) {
            $current = array_pop($stack);
            
            if ($current === $fromId) {
                return true; // Cycle found
            }

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            // Find all outgoing edges from current
            foreach ($this->edges as $edge) {
                if ($edge['from'] === $current) {
                    $stack[] = $edge['to'];
                }
            }
        }

        return false;
    }

    /**
     * Get outgoing edges for a node.
     */
    public function getOutgoingEdges(string $nodeId): array
    {
        return array_filter($this->edges, fn($edge) => $edge['from'] === $nodeId);
    }

    /**
     * Get incoming edges for a node.
     */
    public function getIncomingEdges(string $nodeId): array
    {
        return array_filter($this->edges, fn($edge) => $edge['to'] === $nodeId);
    }
}
