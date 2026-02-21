<?php

namespace Tuzy\Application\Cosmology\Mathematics;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;

/**
 * Non-linear Innovation Burst
 *
 * innovation_rate không linear — tăng đột biến khi entropy cao.
 * Reorganization Law: entropy → innovation spike → cấu trúc mới.
 *
 * Hai tầng: entropy > 0.65 (burst nhẹ), entropy >= 0.9 (burst mạnh — mép sụp đổ).
 */
class InnovationBurst
{
    protected float $entropyTrigger = 0.65;       // Entropy cần để kích hoạt burst
    protected float $entropyCritical = 0.90;      // Entropy rất cao → burst mạnh hơn
    protected float $burstAmplitude = 0.25;
    protected float $burstAmplitudeCritical = 0.40; // Khi entropy >= critical
    protected float $burstProbability = 0.15;
    protected float $burstProbabilityCritical = 0.28; // Cao hơn khi gần sụp

    /**
     * Delta innovation cho tick hiện tại.
     * Linear base + potential burst khi entropy cao (2 tầng).
     */
    public function deltaInnovation(WorldStateVector $s, float $baseDelta): float
    {
        $entropy = $s->getEntropy();
        $innovation = $s->getInnovation();

        $delta = $baseDelta;

        if ($entropy <= $this->entropyTrigger || $innovation >= 0.7) {
            return $delta;
        }

        $excessEntropy = $entropy - $this->entropyTrigger;
        $isCritical = $entropy >= $this->entropyCritical;
        $amplitude = $isCritical ? $this->burstAmplitudeCritical : $this->burstAmplitude;
        $probability = $isCritical ? $this->burstProbabilityCritical : $this->burstProbability;
        $burstPotential = $excessEntropy * $amplitude;

        $burstRoll = ($entropy * 1000) % 100 / 100;
        if ($burstRoll < $probability) {
            $delta += $burstPotential;
        }

        return $delta;
    }

    /**
     * Reorganization Law: khi can_reorganize, tăng innovation — mạnh hơn khi entropy rất cao.
     */
    public function reorganizationBoost(WorldStateVector $s, bool $canReorganize): float
    {
        if (!$canReorganize) {
            return 0.0;
        }
        $entropy = $s->getEntropy();
        $base = ($entropy - $this->entropyTrigger) * 0.1;
        if ($entropy >= $this->entropyCritical) {
            $base += ($entropy - $this->entropyCritical) * 0.2; // Thêm boost khi mép sụp
        }
        return $base;
    }
}
