<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\WorldSnapshot;
use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Tuzy\Domain\Evolution\ValueObject\MetricsSnapshot;

/**
 * QualityEvaluator â€” calculates the GrandnessIndex (GI) for a simulation run.
 *
 * From RFC Â§6.2:
 *   GI = wâ‚Â·MeanDominantEraLength
 *      + wâ‚‚Â·OrderDominanceRatio
 *      + wâ‚ƒÂ·ArcSmoothness
 *      + wâ‚„Â·AvgRebirthGain
 *      - wâ‚…Â·ChaosWithoutRecovery
 *      - wâ‚†Â·FragmentationIndex
 *
 * Semantic metrics (â‰¤30% total weight):
 *   Dâ‚†: Archetype Distribution Entropy
 *   Dâ‚‡: Myth Arc Coherence
 *   Dâ‚ˆ: Semantic Contrast
 *
 * This is a READ-ONLY evaluator. It does not affect simulation.
 */
class QualityEvaluator
{
    // Core metric weights (70%)
    private const W1 = 0.18;  // MeanDominantEraLength
    private const W2 = 0.15;  // OrderDominanceRatio
    private const W3 = 0.12;  // ArcSmoothness
    private const W4 = 0.15;  // AvgRebirthGain
    private const W5 = 0.05;  // ChaosWithoutRecovery (penalty)
    private const W6 = 0.05;  // FragmentationIndex (penalty)

    // Semantic weights (30%)
    private const W7 = 0.10;  // ArchetypeDistributionEntropy
    private const W8 = 0.10;  // MythArcCoherence
    private const W9 = 0.10;  // SemanticContrast

    /**
     * Calculate GrandnessIndex from a simulation trajectory and events.
     *
     * @param WorldSnapshot[] $trajectory Sequence of world snapshots
     * @param array $bifurcationEvents Bifurcation events from the run
     * @return array{grandness_index: float, metrics: array}
     */
    public function evaluate(array $trajectory, array $bifurcationEvents = []): array
    {
        if (count($trajectory) < 2) {
            return ['grandness_index' => 0.0, 'metrics' => []];
        }

        $metrics = [
            'mean_dominant_era_length' => $this->meanDominantEraLength($trajectory),
            'order_dominance_ratio' => $this->orderDominanceRatio($trajectory),
            'arc_smoothness' => $this->arcSmoothness($trajectory),
            'avg_rebirth_gain' => $this->avgRebirthGain($bifurcationEvents),
            'chaos_without_recovery' => $this->chaosWithoutRecovery($trajectory),
            'fragmentation_index' => $this->fragmentationIndex($trajectory),
            'archetype_distribution_entropy' => $this->archetypeDistributionEntropy($trajectory),
            'myth_arc_coherence' => $this->mythArcCoherence($trajectory),
            'semantic_contrast' => $this->semanticContrast($trajectory),
        ];

        $gi = self::W1 * $this->normalize($metrics['mean_dominant_era_length'], 0, 50)
            + self::W2 * $metrics['order_dominance_ratio']
            + self::W3 * $metrics['arc_smoothness']
            + self::W4 * $this->normalize($metrics['avg_rebirth_gain'], 0, 0.5)
            - self::W5 * $metrics['chaos_without_recovery']
            - self::W6 * $metrics['fragmentation_index']
            + self::W7 * $metrics['archetype_distribution_entropy']
            + self::W8 * $metrics['myth_arc_coherence']
            + self::W9 * $metrics['semantic_contrast'];

        $metrics['grandness_index'] = round(max(0.0, min(1.0, $gi)), 4);

        return ['grandness_index' => $metrics['grandness_index'], 'metrics' => $metrics];
    }

    /**
     * Average length of eras where one attractor is dominant.
     */
    private function meanDominantEraLength(array $trajectory): float
    {
        $eras = $this->extractEras($trajectory);
        if (empty($eras)) return 0.0;

        $total = array_sum($eras);
        return $total / count($eras);
    }

    /**
     * Fraction of time stability > 0.5 (orderly periods).
     */
    private function orderDominanceRatio(array $trajectory): float
    {
        $orderly = 0;
        foreach ($trajectory as $snap) {
            if ($snap->cosmic->stability > 0.5) {
                $orderly++;
            }
        }
        return $orderly / max(1, count($trajectory));
    }

    /**
     * Smoothness of state transitions (lower variance in step-to-step delta).
     */
    private function arcSmoothness(array $trajectory): float
    {
        if (count($trajectory) < 3) return 1.0;

        $deltas = [];
        for ($i = 1; $i < count($trajectory); $i++) {
            $delta = abs($trajectory[$i]->cosmic->energy - $trajectory[$i - 1]->cosmic->energy)
                   + abs($trajectory[$i]->cosmic->entropy - $trajectory[$i - 1]->cosmic->entropy);
            $deltas[] = $delta;
        }

        $mean = array_sum($deltas) / count($deltas);
        $variance = 0.0;
        foreach ($deltas as $d) {
            $variance += ($d - $mean) ** 2;
        }
        $variance /= count($deltas);

        // Lower variance â†’ higher smoothness (invert and normalize)
        return max(0.0, 1.0 - sqrt($variance) * 5.0);
    }

    /**
     * Average rebirth gain across bifurcation events.
     */
    private function avgRebirthGain(array $events): float
    {
        if (empty($events)) return 0.0;

        $totalRG = 0.0;
        $count = 0;
        foreach ($events as $event) {
            if (isset($event['force'])) {
                $totalRG += $event['force'] * 0.1; // Approximate RG from force
                $count++;
            }
        }

        return $count > 0 ? $totalRG / $count : 0.0;
    }

    /**
     * Fraction of chaotic periods that didn't result in recovery.
     */
    private function chaosWithoutRecovery(array $trajectory): float
    {
        $chaosSteps = 0;
        $unresolvedChaos = 0;

        for ($i = 1; $i < count($trajectory); $i++) {
            if ($trajectory[$i]->cosmic->stability < 0.3) {
                $chaosSteps++;
                // Check if next step recovered
                if ($i + 1 < count($trajectory) && $trajectory[$i + 1]->cosmic->stability < 0.3) {
                    $unresolvedChaos++;
                }
            }
        }

        return $chaosSteps > 0 ? $unresolvedChaos / $chaosSteps : 0.0;
    }

    /**
     * How fragmented the attractor landscape is (too many switches = bad).
     */
    private function fragmentationIndex(array $trajectory): float
    {
        $switches = 0;
        for ($i = 1; $i < count($trajectory); $i++) {
            if ($trajectory[$i]->cosmic->currentAttractor !== $trajectory[$i - 1]->cosmic->currentAttractor) {
                $switches++;
            }
        }

        $rate = $switches / max(1, count($trajectory));
        return min(1.0, $rate * 10.0); // Normalize: 10% switch rate = max fragmentation
    }

    /**
     * Entropy of archetype distribution (target: 0.6-0.8 â€” diversity without chaos).
     */
    private function archetypeDistributionEntropy(array $trajectory): float
    {
        $counts = [];
        foreach ($trajectory as $snap) {
            $att = $snap->cosmic->currentAttractor;
            $counts[$att] = ($counts[$att] ?? 0) + 1;
        }

        $total = array_sum($counts);
        $entropy = 0.0;

        foreach ($counts as $count) {
            $p = $count / $total;
            if ($p > 0) {
                $entropy -= $p * log($p);
            }
        }

        // Normalize to [0,1] (max entropy = log(n))
        $maxEntropy = log(max(1, count($counts)));
        $normalized = $maxEntropy > 0 ? $entropy / $maxEntropy : 0.0;

        // Best score when 0.6-0.8 (moderate diversity)
        if ($normalized >= 0.6 && $normalized <= 0.8) return 1.0;
        if ($normalized < 0.6) return $normalized / 0.6;
        return max(0.0, 1.0 - ($normalized - 0.8) / 0.2);
    }

    /**
     * Coherence of myth arc (lifecycle stages should progress naturally).
     */
    private function mythArcCoherence(array $trajectory): float
    {
        // Simple proxy: how often does stability trend match energy trend
        $coherent = 0;
        for ($i = 1; $i < count($trajectory); $i++) {
            $stabDelta = $trajectory[$i]->cosmic->stability - $trajectory[$i - 1]->cosmic->stability;
            $energyDelta = $trajectory[$i]->cosmic->energy - $trajectory[$i - 1]->cosmic->energy;

            // Coherent when high energy â†’ high stability (same direction)
            if (($stabDelta >= 0 && $energyDelta >= 0) || ($stabDelta < 0 && $energyDelta < 0)) {
                $coherent++;
            }
        }

        return $coherent / max(1, count($trajectory) - 1);
    }

    /**
     * Semantic contrast between consecutive eras (mid-range is best).
     */
    private function semanticContrast(array $trajectory): float
    {
        $eras = $this->extractEras($trajectory);
        if (count($eras) < 2) return 0.5;

        // Use energy difference between era boundaries as proxy
        $contrasts = [];
        $eraStarts = $this->extractEraStartIndices($trajectory);

        for ($i = 1; $i < count($eraStarts); $i++) {
            $prev = $trajectory[$eraStarts[$i] - 1]->cosmic->energy ?? 0.5;
            $next = $trajectory[$eraStarts[$i]]->cosmic->energy ?? 0.5;
            $contrasts[] = abs($next - $prev);
        }

        if (empty($contrasts)) return 0.5;

        $avgContrast = array_sum($contrasts) / count($contrasts);
        // Best at 0.2-0.4 (moderate contrast)
        if ($avgContrast >= 0.2 && $avgContrast <= 0.4) return 1.0;
        if ($avgContrast < 0.2) return $avgContrast / 0.2;
        return max(0.0, 1.0 - ($avgContrast - 0.4) / 0.6);
    }

    // --- Helpers ---

    private function extractEras(array $trajectory): array
    {
        $eras = [];
        $currentAtt = null;
        $length = 0;

        foreach ($trajectory as $snap) {
            if ($snap->cosmic->currentAttractor !== $currentAtt) {
                if ($currentAtt !== null) $eras[] = $length;
                $currentAtt = $snap->cosmic->currentAttractor;
                $length = 1;
            } else {
                $length++;
            }
        }
        if ($length > 0) $eras[] = $length;

        return $eras;
    }

    private function extractEraStartIndices(array $trajectory): array
    {
        $starts = [0];
        for ($i = 1; $i < count($trajectory); $i++) {
            if ($trajectory[$i]->cosmic->currentAttractor !== $trajectory[$i - 1]->cosmic->currentAttractor) {
                $starts[] = $i;
            }
        }
        return $starts;
    }

    private function normalize(float $value, float $min, float $max): float
    {
        if ($max <= $min) return 0.0;
        return max(0.0, min(1.0, ($value - $min) / ($max - $min)));
    }
}



