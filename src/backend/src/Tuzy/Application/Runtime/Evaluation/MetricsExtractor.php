<?php

namespace Tuzy\Application\Runtime\Evaluation;

use Tuzy\Infrastructure\Cosmology\Repositories\UniverseSnapshotRepository;
use App\Models\UniverseSnapshot;
use Tuzy\Domain\Runtime\ValueObject\UniverseMetrics;

class MetricsExtractor
{
    public function __construct(
        private UniverseSnapshotRepository $snapshotRepository
    ) {
    }

    public function fromLatestSnapshot(string $universeId): ?UniverseMetrics
    {
        $snapshot = $this->snapshotRepository->getLatest($universeId);
        if (!$snapshot) {
            return null;
        }
        return $this->fromSnapshot($snapshot);
    }

    public function fromSnapshot(UniverseSnapshot $snapshot): UniverseMetrics
    {
        $v = $snapshot->state_vector ?? [];
        $entropy = (float) ($v['entropy'] ?? 0.5);
        $order = (float) ($v['order'] ?? 0.5);
        $cohesion = (float) ($v['cohesion'] ?? 0.5);
        $military = (float) ($v['military'] ?? 0.2);
        $innovation = (float) ($v['innovation'] ?? 0.5);

        $complexityIndex = $this->variance($v);
        $stabilityScore = 1.0 - $this->variance($v);
        $conflictDensity = $military * (1.0 - $cohesion);
        $collapseRisk = min(1.0, $entropy * 1.2 + (1.0 - $order) * 0.5);
        $entropyTrend = $entropy;
        $factionDiversity = 0.5;
        $noveltyIndex = $innovation * 0.5 + (1.0 - $order) * 0.3;
        $mutationRate = 0.1;

        return new UniverseMetrics(
            entropyTrend: $entropyTrend,
            complexityIndex: $complexityIndex,
            factionDiversity: $factionDiversity,
            conflictDensity: $conflictDensity,
            stabilityScore: max(0, min(1, $stabilityScore)),
            noveltyIndex: max(0, min(1, $noveltyIndex)),
            mutationRate: $mutationRate,
            collapseRisk: max(0, min(1, $collapseRisk)),
            archetype: null,
            dominantFactionType: null,
        );
    }

    private function variance(array $v): float
    {
        $vals = array_values(array_filter($v, 'is_numeric'));
        if (count($vals) < 2) {
            return 0.0;
        }
        $mean = array_sum($vals) / count($vals);
        $sum = 0.0;
        foreach ($vals as $x) {
            $sum += ($x - $mean) ** 2;
        }
        return (float) ($sum / count($vals));
    }
}
