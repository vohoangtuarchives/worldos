<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\Contracts\AttractorInfluenceSnapshotRepositoryInterface;

/**
 * Groups consecutive ticks with same dominant attractor into phases.
 */
class PhaseDetector
{
    public function __construct(
        private readonly AttractorInfluenceSnapshotRepositoryInterface $influenceRepository
    ) {
    }

    /**
     * @return list<array{start_tick: int, end_tick: int, dominant_attractor: string}>
     */
    public function detectPhases(string $universeId, int $fromTick, int $toTick): array
    {
        $timeline = $this->influenceRepository->getRange($universeId, $fromTick, $toTick);
        if (empty($timeline)) {
            return [];
        }

        $phases = [];
        $current = null;
        foreach ($timeline as $row) {
            $tick = $row['tick'];
            $dom = $row['dominant_attractor'];
            if ($current !== null && $current['dominant_attractor'] === $dom) {
                $current['end_tick'] = $tick;
                continue;
            }
            if ($current !== null) {
                $phases[] = $current;
            }
            $current = ['start_tick' => $tick, 'end_tick' => $tick, 'dominant_attractor' => $dom];
        }
        if ($current !== null) {
            $phases[] = $current;
        }
        return $phases;
    }
}



