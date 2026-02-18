<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\Repositories\EpochRepository;

/**
 * Builds epochs from phases (1:1 for now; can merge consecutive same-dominant phases later).
 */
class EpochManager
{
    public function __construct(
        private readonly PhaseDetector $phaseDetector,
        private readonly EpochRepository $epochRepository
    ) {
    }

    /**
     * Build and persist epochs from influence timeline in range. Does not clear existing epochs.
     *
     * @return list<array{id: int, start_tick: int, end_tick: int, dominant_attractor: string, label: ?string}>
     */
    public function buildFromTimeline(string $universeId, int $fromTick, int $toTick): array
    {
        $phases = $this->phaseDetector->detectPhases($universeId, $fromTick, $toTick);
        $created = [];
        foreach ($phases as $p) {
            $label = $this->labelForAttractor($p['dominant_attractor']);
            $id = $this->epochRepository->insert(
                $universeId,
                $p['start_tick'],
                $p['end_tick'],
                $p['dominant_attractor'],
                $label
            );
            $created[] = [
                'id' => $id,
                'start_tick' => $p['start_tick'],
                'end_tick' => $p['end_tick'],
                'dominant_attractor' => $p['dominant_attractor'],
                'label' => $label,
            ];
        }
        return $created;
    }

    /**
     * @return list<array{id: int, universe_id: string, start_tick: int, end_tick: int, dominant_attractor: string, label: ?string}>
     */
    public function getEpochs(string $universeId, ?int $fromTick = null, ?int $toTick = null): array
    {
        return $this->epochRepository->getByUniverse($universeId, $fromTick, $toTick);
    }

    private function labelForAttractor(string $attractor): string
    {
        return match ($attractor) {
            'STABILITY' => 'Stability',
            'FRAGMENTATION' => 'Fragmentation',
            'WAR' => 'War',
            'STAGNATION' => 'Stagnation',
            'COLLAPSE' => 'Collapse',
            'RENAISSANCE' => 'Renaissance',
            default => $attractor,
        };
    }
}
