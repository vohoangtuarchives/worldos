<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\ValueObjects\CosmicState;
use WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot;

/**
 * CollectiveFieldService â€” Global Collective Memory Field (GCMF).
 *
 * From RFC Â§5.3:
 *   GCMF(t+1) = Î·Â·GCMF(t) + Î£ Contributionáµ¢
 *   Contributionáµ¢ = Î³â‚Â·morph_intensity + Î³â‚‚Â·rebirth_gain - Î³â‚ƒÂ·collapse_depth
 *
 * GCMF produces "epoch mood" â€” influences StyleBias and can trigger
 * emergent archetype proposals.
 *
 * Safety bound: |GCMF bias| â‰¤ 0.25 of total dynamics (RFC Â§4.9)
 */
class CollectiveFieldService
{
    // GCMF decay rate: how fast collective memory fades
    private const ETA = 0.975;

    // Contribution weights
    private const GAMMA_MORPH = 0.05;
    private const GAMMA_REBIRTH = 0.08;
    private const GAMMA_COLLAPSE = 0.04;

    // Bias limits
    private const MAX_GCMF_VALUE = 1.0;
    private const MAX_GCMF_BIAS = 0.25;

    private float $currentValue = 0.0;

    /** @var array<int, float> History of GCMF values per tick */
    private array $history = [];

    public function __construct(float $initialValue = 0.0)
    {
        $this->currentValue = $initialValue;
    }

    /**
     * Update GCMF for a new tick based on all active attractors.
     *
     * @param AttractorAggregate[] $attractors
     * @param int $tick Current simulation tick
     * @return float Updated GCMF value
     */
    public function update(array $attractors, int $tick): float
    {
        // Decay existing value
        $this->currentValue *= self::ETA;

        // Sum contributions from all attractors
        $totalContribution = 0.0;

        foreach ($attractors as $attractor) {
            $contribution = $this->attractorContribution($attractor);
            $totalContribution += $contribution;
        }

        // Normalize by attractor count to prevent unbounded growth
        $count = max(1, count($attractors));
        $this->currentValue += $totalContribution / $count;

        // Clamp
        $this->currentValue = max(-self::MAX_GCMF_VALUE, min(self::MAX_GCMF_VALUE, $this->currentValue));

        // Record history
        $this->history[$tick] = $this->currentValue;

        return $this->currentValue;
    }

    /**
     * Calculate a single attractor's contribution to GCMF.
     *
     * Contribution = Î³â‚Â·morph_intensity + Î³â‚‚Â·rebirth_gain - Î³â‚ƒÂ·collapse_depth
     */
    public function attractorContribution(AttractorAggregate $attractor): float
    {
        $incarnation = $attractor->currentIncarnation();
        if (!$incarnation) return 0.0;

        $morphIntensity = $incarnation->morphIntensity;
        $rebirthGain = $incarnation->rebirthGainFromParent;
        $collapseDepth = $attractor->getCumulativeInstability();

        return self::GAMMA_MORPH * $morphIntensity
             + self::GAMMA_REBIRTH * $rebirthGain
             - self::GAMMA_COLLAPSE * $collapseDepth;
    }

    /**
     * Get the GCMF bias vector (applied to physics params).
     *
     * Positive GCMF â†’ biases toward order/transcendence.
     * Negative GCMF â†’ biases toward entropy/chaos.
     *
     * Magnitude capped at 0.25 (25% of total dynamics).
     */
    public function gcmfBias(): array
    {
        $v = $this->currentValue;
        $absV = min(abs($v), self::MAX_GCMF_BIAS);
        $sign = $v >= 0 ? 1.0 : -1.0;
        $scaled = $absV * $sign;

        return [
            'entropy' => -$scaled * 0.3,      // Positive GCMF â†’ reduces entropy growth
            'energy' => $scaled * 0.2,         // Positive GCMF â†’ slight energy boost
            'causality' => $scaled * 0.15,     // Positive GCMF â†’ more coherence
            'strain' => -$scaled * 0.2,        // Positive GCMF â†’ reduces strain
            'stability' => $scaled * 0.15,     // Positive GCMF â†’ stability boost
        ];
    }

    /**
     * Get the "epoch mood" descriptor based on current GCMF.
     */
    public function epochMood(): string
    {
        $v = $this->currentValue;

        if ($v > 0.5) return 'TRANSCENDENT_ASCENDING';
        if ($v > 0.2) return 'PROSPEROUS_ORDER';
        if ($v > -0.2) return 'BALANCED_NEUTRAL';
        if ($v > -0.5) return 'TURBULENT_DECLINE';
        return 'DARK_COLLAPSE';
    }

    /**
     * Should trigger emergent archetype check?
     *
     * From RFC Â§7.4 â€” trigger conditions include GCMF above threshold.
     */
    public function shouldTriggerEmergentCheck(float $entropyGlobal, float $diversityIndex, float $stabilityMargin): bool
    {
        return $this->currentValue > 0.3
            && $entropyGlobal > 0.4
            && $diversityIndex > 0.5
            && $stabilityMargin > 0.3;
    }

    // --- Getters ---
    public function getCurrentValue(): float { return $this->currentValue; }
    public function getHistory(): array { return $this->history; }

    public function toArray(): array
    {
        return [
            'current_value' => $this->currentValue,
            'history' => $this->history,
        ];
    }

    public static function fromArray(array $data): self
    {
        $service = new self($data['current_value'] ?? 0.0);
        $service->history = $data['history'] ?? [];
        return $service;
    }
}




