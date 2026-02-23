<?php

namespace WorldOS\Legacy\Application\Narrative\Timeline\Services;

use WorldOS\Legacy\Application\Narrative\Timeline\TimelineNode;
use WorldOS\Legacy\Application\Narrative\Character\Entities\Memory;
use WorldOS\Legacy\Application\Narrative\Timeline\Repositories\TimelineEloquentRepository;

class CausalConsistency
{
    public function __construct(
        protected TimelineEloquentRepository $repo
    ) {}

    /**
     * Checks if a memory is accessible from the current timeline node.
     * Accessible = MemoryNode is "Current Node" OR "Ancestor Node".
     */
    public function validateMemoryAccess(TimelineNode $currentNode, Memory $memory): bool
    {
        // If memory is global (no node ID), it's always accessible.
        if (!$memory->timelineNodeId) {
            return true;
        }

        // Check if memory belongs to current node
        if ($memory->timelineNodeId === $currentNode->id) {
            return true;
        }

        // Ancestry Check (BFS/DFS via Parent IDs)
        // This is expensive for deep trees. In production, we'd use closure tables or recursive CTEs.
        // For MVP, we traverse up parents recursively.
        return $this->isAncestor($currentNode, $memory->timelineNodeId);
    }

    protected function isAncestor(TimelineNode $node, string $targetId, int $depth = 0): bool
    {
        if ($depth > 50) return false; // Prevent infinite loops
        
        foreach ($node->parentIds as $parentId) {
            if ($parentId === $targetId) {
                return true;
            }
            
            // Fetch parent node to continue traversal
            $parent = $this->repo->findById($parentId);
            if ($parent && $this->isAncestor($parent, $targetId, $depth + 1)) {
                return true;
            }
        }

        return false;
    }
}
