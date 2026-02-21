<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\Services;

use Tuzy\Domain\Cosmology\Contracts\AttractorInfluenceSnapshotRepositoryInterface;

/**
 * Builds meta-history summary from epochs and turning points for narrative layer.
 */
class MetaHistoryBuilder
{
    public function __construct(
        private readonly EpochManager $epochManager,
        private readonly TurningPointEngine $turningPointEngine,
        private readonly AttractorInfluenceSnapshotRepositoryInterface $influenceRepository
    ) {
    }

    /**
     * @return array{eras: list<array{label: string, start_tick: int, end_tick: int, dominant_attractor: string}>, turning_points: list<array{tick: int, type: string, payload: ?array}>}
     */
    public function build(string $universeId, ?int $fromTick = null, ?int $toTick = null): array
    {
        $epochs = $this->epochManager->getEpochs($universeId, $fromTick, $toTick);
        $turningPoints = $this->turningPointEngine->getByUniverse($universeId, $fromTick, $toTick);

        $eras = [];
        $i = 0;
        foreach ($epochs as $e) {
            $i++;
            $eras[] = [
                'label' => $e['label'] ?? ('Era ' . $i . ' – ' . ($e['dominant_attractor'] ?? 'Unknown')),
                'start_tick' => $e['start_tick'],
                'end_tick' => $e['end_tick'],
                'dominant_attractor' => $e['dominant_attractor'],
            ];
        }

        return [
            'eras' => $eras,
            'turning_points' => array_map(fn ($tp) => [
                'tick' => $tp['tick'],
                'type' => $tp['type'],
                'payload' => $tp['payload'],
            ], $turningPoints),
        ];
    }

    /**
     * Short summary string for narrative context (e.g. "Era I Stability, Era II Fragmentation").
     */
    public function buildSummaryString(string $universeId, ?int $fromTick = null, ?int $toTick = null): string
    {
        $data = $this->build($universeId, $fromTick, $toTick);
        $parts = [];
        foreach ($data['eras'] as $era) {
            $parts[] = $era['label'];
        }
        if (empty($parts)) {
            return 'Chưa có kỷ nguyên được ghi nhận.';
        }
        return implode('; ', $parts);
    }

    /**
     * Build summary from pre-computed phases (e.g. from PhaseDetector) and turning points.
     *
     * @param list<array{start_tick: int, end_tick: int, dominant_attractor: string}> $phases
     * @param list<array{tick: int, type: string, payload: ?array}> $turningPoints
     */
    public function buildFromPhases(array $phases, array $turningPoints): array
    {
        $eras = [];
        $i = 0;
        foreach ($phases as $p) {
            $i++;
            $label = $p['dominant_attractor'] ?? 'Unknown';
            $eras[] = [
                'label' => 'Era ' . $i . ' – ' . $label,
                'start_tick' => $p['start_tick'],
                'end_tick' => $p['end_tick'],
                'dominant_attractor' => $p['dominant_attractor'],
            ];
        }
        return [
            'eras' => $eras,
            'turning_points' => $turningPoints,
        ];
    }
}
