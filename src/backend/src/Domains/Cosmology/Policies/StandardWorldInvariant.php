<?php

declare(strict_types=1);

namespace WorldOS\Domains\Cosmology\Policies;

use WorldOS\Domains\Cosmology\Contracts\WorldInvariantInterface;
use WorldOS\Domains\Cosmology\World;

/**
 * StandardWorldInvariant
 * 
 * Re-implementation of the V3 core physics check logic.
 * Ensures the basic ontological stability of the World.
 */
class StandardWorldInvariant implements WorldInvariantInterface
{
    private ?string $violationMessage = null;

    public function validate(World $world): bool
    {
        $laws = $world->getPhysicalLaws();

        // 1. Threshold integrity
        if ($laws->stabilityThreshold <= 0 || $laws->stabilityThreshold > 1.0) {
            $this->violationMessage = "CẢNH BÁO VẬT LÝ: Ngưỡng ổn định ({$laws->stabilityThreshold}) nằm ngoài tầm kiểm soát [0-1].";
            return false;
        }

        // 2. Entropy rates
        if ($laws->entropyRate < 0) {
            $this->violationMessage = "CẢNH BÁO VẬT LÝ: Tốc độ Entropy không được là số âm.";
            return false;
        }

        // 3. Causality
        if ($laws->causalityStrength < 0) {
            $this->violationMessage = "CẢNH BÁO VẬT LÝ: Liên kết nhân quả bị đứt gãy (Negative Causality).";
            return false;
        }

        return true;
    }

    public function getViolationMessage(): ?string
    {
        return $this->violationMessage;
    }
}
