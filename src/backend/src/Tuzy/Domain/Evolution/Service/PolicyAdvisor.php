<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\RiskForecast;

/**
 * PolicyAdvisor - Takes the RiskForecast and suggests actions.
 * Operates purely based on the AI's understanding of reality, ignoring political friction.
 */
final class PolicyAdvisor
{
    public const POLICY_TRIGGER_REFORM = 'TRIGGER_REFORM';
    public const POLICY_INCREASE_INNOVATION = 'INCREASE_INNOVATION';
    public const POLICY_REDUCE_CENTRALIZATION = 'REDUCE_CENTRALIZATION';
    public const POLICY_INCREASE_DATA_QUALITY = 'INCREASE_DATA_QUALITY';
    public const POLICY_MAINTAIN_STATUS_QUO = 'MAINTAIN_STATUS_QUO';

    /**
     * @return string[] Array of suggested policy constants, sorted by priority
     */
    public function suggest(RiskForecast $forecast): array
    {
        $policies = [];

        if ($forecast->collapseProbability > 0.6 && $forecast->reformSuccessProbability > 0.4) {
            $policies[] = self::POLICY_TRIGGER_REFORM;
        }

        if ($forecast->entropyTrajectory > 0.7) {
            $policies[] = self::POLICY_REDUCE_CENTRALIZATION;
        }

        $maxShock = max(array_values($forecast->shockRiskVector) ?: [0.0]);
        if ($maxShock > 0.5) {
            $policies[] = self::POLICY_INCREASE_INNOVATION;
        }

        // If high prediction error, suggest investing in intelligence mapping
        if ($forecast->predictionError > 0.3) {
            $policies[] = self::POLICY_INCREASE_DATA_QUALITY;
        }

        if (empty($policies)) {
            $policies[] = self::POLICY_MAINTAIN_STATUS_QUO;
        }

        return $policies;
    }
}
