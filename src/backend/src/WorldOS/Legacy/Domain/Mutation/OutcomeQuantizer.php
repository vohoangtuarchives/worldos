<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Mutation;

use WorldOS\Saga\Domain\Narrative\ValueObject\DefaultOutcome;
use WorldOS\Saga\Domain\Narrative\ValueObject\StoryOutcomeDTO;

/**
 * Validates user override, clamps to plausible range, produces final StoryOutcomeDTO.
 * Shadow multiplier (e.g. 0.3) is applied by pipeline when !isConfirmed, not stored in DTO.
 */
class OutcomeQuantizer
{
    private const SHADOW_MULTIPLIER = 0.3;

    public function __construct(
        private readonly float $maxIntensityStep = 0.4,
    ) {
    }

    /**
     * Quantize default outcome with optional user override. Returns final StoryOutcomeDTO.
     */
    public function quantize(
        DefaultOutcome $default,
        ?string $userOverride,
        ?float $overrideIntensity,
        bool $isConfirmed,
        ?string $arcId = null,
    ): StoryOutcomeDTO {
        $result = $default->result;
        $intensity = $default->intensity;

        if ($userOverride !== null && $userOverride !== '') {
            $valid = [StoryOutcomeDTO::RESULT_WIN, StoryOutcomeDTO::RESULT_LOSE, StoryOutcomeDTO::RESULT_PARTIAL];
            if (in_array($userOverride, $valid, true)) {
                $result = $userOverride;
            }
            if ($overrideIntensity !== null && $overrideIntensity >= 0.0 && $overrideIntensity <= 1.0) {
                $intensity = $overrideIntensity;
            } else {
                $intensity = $this->clampIntensityToPlausible($intensity, $result, $default->intensity);
            }
        } else {
            $intensity = $this->clampIntensityToPlausible($intensity, $result, $default->intensity);
        }

        return new StoryOutcomeDTO($result, $intensity, $default->scope, $isConfirmed, $arcId);
    }

    /**
     * Multiplier for mutation magnitude: 1.0 when confirmed, shadow (0.3) otherwise.
     */
    public function magnitudeMultiplier(bool $isConfirmed): float
    {
        return $isConfirmed ? 1.0 : self::SHADOW_MULTIPLIER;
    }

    private function clampIntensityToPlausible(float $intensity, string $result, float $defaultIntensity): float
    {
        $maxChange = $this->maxIntensityStep;
        $clamped = max(0.0, min(1.0, $intensity));
        $diff = abs($clamped - $defaultIntensity);
        if ($diff > $maxChange) {
            $sign = $clamped > $defaultIntensity ? 1.0 : -1.0;
            $clamped = $defaultIntensity + $sign * $maxChange;
        }
        return max(0.0, min(1.0, $clamped));
    }
}
