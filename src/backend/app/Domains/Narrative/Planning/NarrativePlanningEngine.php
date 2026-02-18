<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

/**
 * Phase 5.2: Arc segmentation and chapter blueprints from world state / pressure curve.
 */
class NarrativePlanningEngine
{
    /**
     * Plan arcs from world state and pressure points (major/minor arcs, turning points).
     *
     * @param array<string, mixed> $worldState
     * @param array<int, array{type?: string, value?: float, label?: string}> $pressurePoints
     * @return list<array{title: string, arc_type: string, segments: list<array{tick_from: int, tick_to: int, emotional_beat: string}>}>
     */
    public function planArcs(array $worldState, array $pressurePoints): array
    {
        $arcs = [];
        $segmentCount = max(1, (int) floor(count($pressurePoints) / 3));
        $arcs[] = [
            'title' => 'Primary arc',
            'arc_type' => $worldState['structural_anchor'] ?? 'tension',
            'segments' => array_map(fn ($i) => [
                'tick_from' => $i * 5,
                'tick_to' => ($i + 1) * 5,
                'emotional_beat' => ['rising', 'climax', 'falling', 'resolution'][$i % 4],
            ], range(0, $segmentCount - 1)),
        ];
        return $arcs;
    }

    /**
     * Produce chapter blueprints for an arc (emotional objective, conflict delta, motif targets).
     *
     * @param array{title: string, arc_type: string, segments: list<array>} $arc
     * @return list<array{chapter_index: int, emotional_objective: string, conflict_delta: array, motif_targets: array}>
     */
    public function planChapters(array $arc): array
    {
        $blueprints = [];
        foreach ($arc['segments'] as $i => $seg) {
            $blueprints[] = [
                'chapter_index' => $i,
                'emotional_objective' => $seg['emotional_beat'] ?? 'tension',
                'conflict_delta' => ['direction' => $i % 2 === 0 ? 'increase' : 'decrease'],
                'motif_targets' => [],
            ];
        }
        return $blueprints;
    }
}
