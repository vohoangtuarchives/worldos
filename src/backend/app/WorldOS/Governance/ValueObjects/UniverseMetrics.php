<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\ValueObjects;

/**
 * Universe Metrics — extracted performance indicators.
 *
 * From docs §13.2: MetricsExtractor → entropy_trend, complexity_index,
 * stability_score, collapse_risk, innovation_trend, ip_score.
 *
 * Calculated from snapshot history, not raw state vectors.
 * Pure PHP — NO Laravel dependencies.
 */
final readonly class UniverseMetrics
{
    public function __construct(
        public float $entropyTrend,       // -1 (decreasing) to +1 (increasing)
        public float $complexityIndex,    // 0 (simple) to 1 (complex)
        public float $stabilityScore,     // 0 (chaotic) to 1 (stable)
        public float $collapseRisk,       // 0 (safe) to 1 (imminent collapse)
        public float $innovationTrend,    // -1 (stagnating) to +1 (accelerating)
        public float $ipScore,            // 0 (boring) to 1 (brilliant IP potential)
        public int $ticksAnalyzed,        // How many ticks of history used
    ) {
    }

    /**
     * Quick assessment: is this universe "interesting" for IP generation?
     */
    public function isInteresting(): bool
    {
        return $this->ipScore >= 0.6;
    }

    /**
     * Is collapse likely within next few ticks?
     */
    public function isAtRisk(): bool
    {
        return $this->collapseRisk >= 0.7;
    }

    /**
     * Is the universe in a stagnant state (boring)?
     */
    public function isStagnant(): bool
    {
        return $this->complexityIndex < 0.2
            && abs($this->entropyTrend) < 0.05
            && $this->innovationTrend < 0.05;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'entropy_trend' => round($this->entropyTrend, 4),
            'complexity_index' => round($this->complexityIndex, 4),
            'stability_score' => round($this->stabilityScore, 4),
            'collapse_risk' => round($this->collapseRisk, 4),
            'innovation_trend' => round($this->innovationTrend, 4),
            'ip_score' => round($this->ipScore, 4),
            'ticks_analyzed' => $this->ticksAnalyzed,
        ];
    }
}
