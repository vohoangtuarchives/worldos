<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\Services;

use WorldOS\Saga\Domain\Legacy\SagaWorld;

/**
 * Phase 4.2: Controls exploration vs exploitation; centroid of best outcomes; gradient pull toward centroid.
 */
final class ConvergenceController
{
    private const PULL_CLAMP_DELTA = 0.15;
    private const CENTROID_RESILIENCE_THRESHOLD = 0.5;

    public function __construct(
        private readonly float $explorationMin = 0.02,
        private readonly float $decayHalfLife = 20.0,
    ) {
    }

    public static function fromConfig(): self
    {
        $config = config('saga', []);
        return new self(
            (float) ($config['convergence_exploration_min'] ?? 0.02),
            (float) ($config['convergence_decay_half_life'] ?? 20.0),
        );
    }

    /**
     * Exploration factor for next world (0 = full exploitation, 1 = full exploration). Decreases with sequence.
     * Formula: max(exploration_min, 0.1 * exp(-sequence / decay_half_life)).
     */
    public function explorationFactor(int $worldSequence): float
    {
        $raw = 0.1 * exp(-$worldSequence / max(0.01, $this->decayHalfLife));
        return max($this->explorationMin, $raw);
    }

    /**
     * Centroid of "good" outcomes for this saga (resilience/stability above threshold).
     * Returns null if insufficient data.
     *
     * @return array{stability: float, resilience: float, entropy_control: float}|null
     */
    public function centroidForSaga(int $sagaId, int $lastN = 10): ?array
    {
        $worlds = SagaWorld::where('saga_id', $sagaId)
            ->whereNotNull('collapse_context')
            ->orderByDesc('sequence')
            ->limit($lastN * 2)
            ->get();

        $vectors = [];
        foreach ($worlds as $sw) {
            $ctx = $sw->collapse_context;
            if (!is_array($ctx) || empty($ctx['objective_vector'])) {
                continue;
            }
            $ov = $ctx['objective_vector'];
            $resilience = (float) ($ov['resilience'] ?? 0);
            if ($resilience < self::CENTROID_RESILIENCE_THRESHOLD) {
                continue;
            }
            $vectors[] = [
                'stability' => (float) ($ov['stability'] ?? 0.5),
                'resilience' => $resilience,
                'entropy_control' => (float) ($ov['entropy_control'] ?? 0.5),
            ];
        }

        if (count($vectors) < 1) {
            return null;
        }

        $sums = ['stability' => 0.0, 'resilience' => 0.0, 'entropy_control' => 0.0];
        foreach ($vectors as $v) {
            $sums['stability'] += $v['stability'];
            $sums['resilience'] += $v['resilience'];
            $sums['entropy_control'] += $v['entropy_control'];
        }
        $n = (float) count($vectors);
        return [
            'stability' => $sums['stability'] / $n,
            'resilience' => $sums['resilience'] / $n,
            'entropy_control' => $sums['entropy_control'] / $n,
        ];
    }

    /**
     * Blend current bias toward centroid. Returns new bias: current + strength * (centroid - current), clamped per key.
     *
     * @param array<string, float> $currentBias
     * @param array<string, float> $centroid
     * @return array<string, float>
     */
    public function pullTowardCentroid(array $currentBias, array $centroid, float $strength): array
    {
        $result = [];
        $keys = array_unique(array_merge(array_keys($currentBias), array_keys($centroid)));
        foreach ($keys as $k) {
            $cur = (float) ($currentBias[$k] ?? 0.0);
            $cen = (float) ($centroid[$k] ?? 0.0);
            $delta = $strength * ($cen - $cur);
            $delta = max(-self::PULL_CLAMP_DELTA, min(self::PULL_CLAMP_DELTA, $delta));
            $result[$k] = $cur + $delta;
        }
        return $result;
    }
}
