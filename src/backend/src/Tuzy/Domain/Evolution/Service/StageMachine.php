<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\CivilizationStage;

/**
 * Stage Machine â€” 7-phase evolution loop.
 * Transitions based on Pressure and structural metrics only (no narrative).
 */
final class StageMachine
{
    /**
     * Compute next stage from current stage and pressure (and optional criticality assessment).
     *
     * @param CivilizationStage $current
     * @param float $pressure 0..1
     * @param array{should_collapse?: bool, can_reorganize?: bool} $assessment Optional from CriticalityDetector
     */
    public function transition(
        CivilizationStage $current,
        float $pressure,
        array $assessment = []
    ): CivilizationStage {
        $shouldCollapse = $assessment['should_collapse'] ?? ($pressure >= 0.9);
        $canReorganize = $assessment['can_reorganize'] ?? false;

        return match ($current) {
            CivilizationStage::GENESIS => $pressure < 0.3 ? CivilizationStage::GENESIS : CivilizationStage::EXPANSION,
            CivilizationStage::EXPANSION => $pressure >= 0.4 ? CivilizationStage::TENSION : CivilizationStage::EXPANSION,
            CivilizationStage::TENSION => $pressure >= 0.7 ? CivilizationStage::CRISIS : CivilizationStage::TENSION,
            CivilizationStage::CRISIS => $shouldCollapse ? CivilizationStage::COLLAPSE : CivilizationStage::CRISIS,
            CivilizationStage::COLLAPSE => CivilizationStage::RECONFIGURATION,
            CivilizationStage::RECONFIGURATION => $pressure < 0.6 ? CivilizationStage::STABILIZATION : CivilizationStage::RECONFIGURATION,
            CivilizationStage::STABILIZATION => $pressure < 0.35 ? CivilizationStage::EXPANSION : CivilizationStage::STABILIZATION,
        };
    }

    /** Parse stage from string (e.g. from parameters). */
    public static function fromString(string $stage): CivilizationStage
    {
        return CivilizationStage::tryFrom(strtoupper($stage)) ?? CivilizationStage::GENESIS;
    }
}



