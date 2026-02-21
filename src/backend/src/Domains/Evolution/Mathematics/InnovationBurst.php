<?php

namespace WorldOS\Domains\Evolution\Mathematics;

use WorldOS\Domains\Evolution\ValueObjects\WorldStateVector;

/**
 * Non-linear Innovation Burst
 *
 * innovation_rate khÃ´ng linear â€” tÄƒng Ä‘á»™t biáº¿n khi entropy cao.
 * Reorganization Law: entropy â†’ innovation spike â†’ cáº¥u trÃºc má»›i.
 *
 * Hai táº§ng: entropy > 0.65 (burst nháº¹), entropy >= 0.9 (burst máº¡nh â€” mÃ©p sá»¥p Ä‘á»•).
 */
class InnovationBurst
{
    protected float $entropyTrigger = 0.65;       // Entropy cáº§n Ä‘á»ƒ kÃ­ch hoáº¡t burst
    protected float $entropyCritical = 0.90;      // Entropy ráº¥t cao â†’ burst máº¡nh hÆ¡n
    protected float $burstAmplitude = 0.25;
    protected float $burstAmplitudeCritical = 0.40; // Khi entropy >= critical
    protected float $burstProbability = 0.15;
    protected float $burstProbabilityCritical = 0.28; // Cao hÆ¡n khi gáº§n sá»¥p

    /**
     * Delta innovation cho tick hiá»‡n táº¡i.
     * Linear base + potential burst khi entropy cao (2 táº§ng).
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
     * Reorganization Law: khi can_reorganize, tÄƒng innovation â€” máº¡nh hÆ¡n khi entropy ráº¥t cao.
     */
    public function reorganizationBoost(WorldStateVector $s, bool $canReorganize): float
    {
        if (!$canReorganize) {
            return 0.0;
        }
        $entropy = $s->getEntropy();
        $base = ($entropy - $this->entropyTrigger) * 0.1;
        if ($entropy >= $this->entropyCritical) {
            $base += ($entropy - $this->entropyCritical) * 0.2; // ThÃªm boost khi mÃ©p sá»¥p
        }
        return $base;
    }
}


