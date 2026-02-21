<?php

namespace Tuzy\Domain\Saga;

use Tuzy\Domain\Timeline\TimelineDAG;

/**
 * TimelineBuilder - Add saga events to timeline DAG
 * 
 * Builds temporal coherence for narrative events.
 */
class TimelineBuilder
{
    public function __construct(
        private TimelineDAG $dag
    ) {}

    /**
     * Add saga events to timeline.
     * 
     * @param array $events Detected saga events
     * @param int $epoch Current epoch
     * @param string $worldId World ID
     * @return array Added node IDs
     */
    public function addEvents(array $events, int $epoch, string $worldId): array
    {
        $nodeIds = [];

        foreach ($events as $event) {
            $nodeId = $this->addEventNode($event, $epoch, $worldId);
            $nodeIds[] = $nodeId;
        }

        return $nodeIds;
    }

    /**
     * Add single event node to timeline.
     */
    private function addEventNode(array $event, int $epoch, string $worldId): string
    {
        $nodeData = [
            'type' => 'saga_event',
            'event_type' => $event['type'],
            'category' => $event['category'] ?? 'general',
            'severity' => $event['severity'] ?? 0.5,
            'epoch' => $epoch,
            'world_id' => $worldId,
            'data' => $event['data'] ?? [],
            'narrative_template' => $event['narrative_template'] ?? 'default',
        ];

        return $this->dag->addNode($nodeData);
    }

    /**
     * Link event to previous epoch events (temporal ordering).
     */
    public function linkTemporalSequence(array $currentNodeIds, array $previousNodeIds): void
    {
        foreach ($currentNodeIds as $currentId) {
            foreach ($previousNodeIds as $previousId) {
                $this->dag->addEdge($previousId, $currentId, 'temporal_sequence');
            }
        }
    }

    /**
     * Link causally related events.
     */
    public function linkCausalEvents(string $causeNodeId, string $effectNodeId): void
    {
        $this->dag->addEdge($causeNodeId, $effectNodeId, 'causal_link');
    }

    /**
     * Get event timeline for epoch range.
     */
    public function getEventTimeline(string $worldId, int $fromEpoch, int $toEpoch): array
    {
        $nodes = $this->dag->getNodes();
        
        return array_filter($nodes, function ($node) use ($worldId, $fromEpoch, $toEpoch) {
            return $node['type'] === 'saga_event' &&
                   $node['world_id'] === $worldId &&
                   $node['epoch'] >= $fromEpoch &&
                   $node['epoch'] <= $toEpoch;
        });
    }

    /**
     * Export timeline as JSON.
     */
    public function exportTimeline(string $worldId, int $fromEpoch, int $toEpoch): string
    {
        $timeline = $this->getEventTimeline($worldId, $fromEpoch, $toEpoch);
        
        return json_encode([
            'world_id' => $worldId,
            'epoch_range' => [$fromEpoch, $toEpoch],
            'events' => array_values($timeline),
            'event_count' => count($timeline),
        ], JSON_PRETTY_PRINT);
    }
}
