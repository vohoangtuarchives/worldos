<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

use App\Domains\Narrative\DTO\BeatSpec;

/**
 * Deterministic planner: outputs BeatSpec (emotion, tension, arc context) from arc position
 * and optional world state. No LLM; hybrid curve + fixed beat list.
 */
class BeatPlanner
{
    /** Fixed emotional beats per chapter index (same as SerialArcPlanner). */
    private const BEATS = ['opening', 'rising', 'complication', 'midpoint', 'crisis', 'resolution'];

    /**
     * Plan a single BeatSpec for the next chapter.
     *
     * @param array{book_index: int, title: string, one_line: string} $arc
     * @param array<string, float>|null $worldState From universe->getState()->getAll() (entropy, order, ...)
     * @param array<string, mixed>|null $parameters From universe->getParameters() (arc_phase, ...)
     */
    public function planForChapter(
        array $arc,
        int $chapterIndexInArc,
        int $chaptersPerArc,
        ?array $worldState = null,
        ?array $parameters = null
    ): BeatSpec {
        $arcContext = $arc['one_line'] ?? '';

        $arcProgress = $chaptersPerArc > 0
            ? (float) ($chapterIndexInArc / $chaptersPerArc)
            : 0.0;
        $arcProgress = min(1.0, max(0.0, $arcProgress));

        $baseTension = $this->baseTension($arcProgress);
        $entropyFactor = 0.0;
        if ($worldState !== null && isset($worldState['entropy'])) {
            $entropyFactor = (float) $worldState['entropy'] * 0.2;
        }
        $tension = min(1.0, max(0.0, $baseTension + $entropyFactor));

        $emotion = self::BEATS[$chapterIndexInArc % count(self::BEATS)];

        $worldSignals = [];
        if ($worldState !== null) {
            $worldSignals = array_intersect_key(
                $worldState,
                array_flip(['entropy', 'order', 'cohesion', 'innovation'])
            );
        }

        return new BeatSpec(
            emotion: $emotion,
            tension: $tension,
            arcContext: $arcContext,
            primaryCharacters: [],
            worldSignals: $worldSignals
        );
    }

    /**
     * Sigmoid-like base tension: low early arc, rising mid, high late arc.
     */
    private function baseTension(float $progress): float
    {
        if ($progress <= 0.0) {
            return 0.2;
        }
        if ($progress >= 1.0) {
            return 0.9;
        }
        return (float) (1.0 / (1.0 + exp(-8.0 * ($progress - 0.5))));
    }
}
