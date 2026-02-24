<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\Services;

use App\WorldOS\Governance\ValueObjects\UniverseMetrics;
use App\WorldOS\Runtime\Entities\UniverseEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseSnapshot;

/**
 * Metrics Extractor — extracts trends from Universe snapshot history.
 *
 * From docs §13.2: MetricsExtractor → CollapseRisk, InnovationTrend.
 * Does NOT expose raw state_vector to evaluation layer.
 *
 * Pure computation — NO side effects, NO Laravel dependencies.
 */
final class MetricsExtractor
{
    /**
     * Extract metrics from a Universe and its snapshot history.
     *
     * @param UniverseSnapshot[] $snapshots Ordered by tick ascending
     */
    public function extract(UniverseEntity $universe, array $snapshots): UniverseMetrics
    {
        $count = count($snapshots);

        if ($count === 0) {
            return $this->emptyMetrics();
        }

        $latest = end($snapshots);
        $state = $latest->stateVector;

        // Entropy trend: compare first half vs second half
        $entropyTrend = $this->calculateTrend($snapshots, 'entropy');

        // Innovation trend
        $innovationTrend = $this->calculateTrend($snapshots, 'innovation');

        // Complexity index: high when many dimensions are active (non-extreme)
        $complexityIndex = $this->calculateComplexity($state);

        // Stability score: inverse of variance in recent snapshots
        $stabilityScore = $this->calculateStability($snapshots);

        // Collapse risk: high entropy + low cohesion + low order
        $collapseRisk = $this->calculateCollapseRisk($state);

        // IP score: narrative potential (interesting things happening)
        $ipScore = $this->calculateIpScore(
            $complexityIndex,
            $entropyTrend,
            $innovationTrend,
            $collapseRisk,
            $stabilityScore,
        );

        return new UniverseMetrics(
            entropyTrend: $entropyTrend,
            complexityIndex: $complexityIndex,
            stabilityScore: $stabilityScore,
            collapseRisk: $collapseRisk,
            innovationTrend: $innovationTrend,
            ipScore: $ipScore,
            ticksAnalyzed: $count,
        );
    }

    private function emptyMetrics(): UniverseMetrics
    {
        return new UniverseMetrics(
            entropyTrend: 0.0,
            complexityIndex: 0.0,
            stabilityScore: 1.0,
            collapseRisk: 0.0,
            innovationTrend: 0.0,
            ipScore: 0.0,
            ticksAnalyzed: 0,
        );
    }

    /**
     * Calculate trend of a specific dimension over snapshot history.
     * Returns -1 (decreasing) to +1 (increasing).
     *
     * @param UniverseSnapshot[] $snapshots
     */
    private function calculateTrend(array $snapshots, string $dimension): float
    {
        $count = count($snapshots);
        if ($count < 2) {
            return 0.0;
        }

        $midpoint = intdiv($count, 2);
        $firstHalf = array_slice($snapshots, 0, $midpoint);
        $secondHalf = array_slice($snapshots, $midpoint);

        $avgFirst = $this->averageDimension($firstHalf, $dimension);
        $avgSecond = $this->averageDimension($secondHalf, $dimension);

        return max(-1.0, min(1.0, ($avgSecond - $avgFirst) * 5.0));
    }

    /**
     * @param UniverseSnapshot[] $snapshots
     */
    private function averageDimension(array $snapshots, string $dimension): float
    {
        if (empty($snapshots)) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($snapshots as $snap) {
            $sum += $snap->stateVector->{$dimension} ?? 0.0;
        }

        return $sum / count($snapshots);
    }

    /**
     * Complexity = how many dimensions are "active" (between 0.15 and 0.85).
     */
    private function calculateComplexity(object $state): float
    {
        $dimensions = ['entropy', 'order', 'cohesion', 'innovation', 'inequality', 'legitimacy', 'trauma'];
        $active = 0;

        foreach ($dimensions as $dim) {
            $val = $state->{$dim} ?? 0.0;
            if ($val > 0.15 && $val < 0.85) {
                $active++;
            }
        }

        return $active / count($dimensions);
    }

    /**
     * Stability = low variance in recent entropy readings.
     *
     * @param UniverseSnapshot[] $snapshots
     */
    private function calculateStability(array $snapshots): float
    {
        $recent = array_slice($snapshots, -min(10, count($snapshots)));
        if (count($recent) < 2) {
            return 1.0;
        }

        $values = array_map(fn($s) => $s->stateVector->entropy, $recent);
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);

        // Low variance = high stability
        return max(0.0, min(1.0, 1.0 - sqrt($variance) * 5.0));
    }

    /**
     * Collapse risk from current state.
     */
    private function calculateCollapseRisk(object $state): float
    {
        $entropy = $state->entropy ?? 0.0;
        $cohesion = $state->cohesion ?? 0.5;
        $order = $state->order ?? 0.5;
        $innovation = $state->innovation ?? 0.5;

        // High entropy + low cohesion + low order = collapse
        $risk = ($entropy * 0.4) + ((1.0 - $cohesion) * 0.3) + ((1.0 - $order) * 0.2) + ((1.0 - $innovation) * 0.1);

        return max(0.0, min(1.0, $risk));
    }

    /**
     * IP Score = narrative potential.
     * High when: complex, changing, not too stable, not yet collapsed.
     */
    private function calculateIpScore(
        float $complexity,
        float $entropyTrend,
        float $innovationTrend,
        float $collapseRisk,
        float $stability,
    ): float {
        // Interesting = complex + changing + moderate risk
        $dynamism = abs($entropyTrend) * 0.3 + abs($innovationTrend) * 0.3;
        $tension = min($collapseRisk, 0.8) * 0.5; // High risk is good drama, but not too high
        $richness = $complexity * 0.4;

        // Penalty for too stable or too collapsed
        $penalty = 0.0;
        if ($stability > 0.9) {
            $penalty += 0.2; // Boring
        }
        if ($collapseRisk > 0.9) {
            $penalty += 0.3; // About to end
        }

        $score = $dynamism + $tension + $richness - $penalty;

        return max(0.0, min(1.0, $score));
    }
}
