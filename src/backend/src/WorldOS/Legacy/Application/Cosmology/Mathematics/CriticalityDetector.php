<?php

namespace WorldOS\Legacy\Application\Cosmology\Mathematics;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\ConstraintProfile;

/**
 * Criticality Detector — Tính toán khi nào đạt điểm chuyển pha (phase transition)
 *
 * Supports 3 zones: Stability (<0.4), Metastable (0.4–0.75), Critical (≥0.75).
 * Cascade severity = P^3 when in Critical zone.
 * Legacy 4-phase and collapse/reorganize logic kept for backward compatibility.
 */
class CriticalityDetector
{
    public const PHASE_STABLE = 'STABLE';
    public const PHASE_REORGANIZATION_POSSIBLE = 'REORGANIZATION_POSSIBLE';
    public const PHASE_CRITICAL = 'CRITICAL';
    public const PHASE_COLLAPSE_IMMINENT = 'COLLAPSE_IMMINENT';

    /** Pressure zones (non-linear model) */
    public const ZONE_STABILITY = 'STABILITY';       // P < 0.4
    public const ZONE_METASTABLE = 'METASTABLE';     // 0.4 <= P < 0.75
    public const ZONE_CRITICAL = 'CRITICAL';          // P >= 0.75

    /** Collapse trigger: contradiction > X AND innovation < Y AND resource_flow < Z → structural fracture */
    public const COLLAPSE_CONTRADICTION_THRESHOLD = 0.70;
    public const COLLAPSE_INNOVATION_MIN = 0.15;
    public const COLLAPSE_RESOURCE_FLOW_THRESHOLD = 0.05;

    protected float $contradictionThreshold = 0.70;
    protected float $innovationMinThreshold = 0.15;
    protected float $resourceFlowThreshold = 0.05;
    protected float $entropyForReorganization = 0.65;

    public function __construct(
        protected ?PressureAccumulationField $pressureField = null
    ) {
        $this->pressureField = $pressureField ?? new PressureAccumulationField();
    }

    /**
     * Determine current phase and distance to criticality.
     *
     * @param WorldStateVector $s
     * @param float $accumulatedPressure Previous smoothed pressure (for legacy or when no profile).
     * @param ConstraintProfile|null $profile When set, use non-linear pressure and zone.
     * @return array{phase: string, zone?: string, distance_to_critical: float, should_collapse: bool, can_reorganize: bool, pressure: float, cascade_severity?: float}
     */
    public function assess(
        WorldStateVector $s,
        float $accumulatedPressure = 0.0,
        ?ConstraintProfile $profile = null
    ): array {
        $innovation = $s->getInnovation();
        $resourceFlow = $s->getResourceStock();
        $entropy = $s->getEntropy();
        $contradiction = $this->pressureField->contradictionIndex($s);

        if ($profile !== null) {
            $pressure = $this->pressureField->pressureSmoothed($s, $profile, $accumulatedPressure);
            $zone = $this->zoneFromPressure($pressure);
            $cascadeSeverity = $zone === self::ZONE_CRITICAL ? $pressure * $pressure * $pressure : 0.0;
        } else {
            $pressure = $accumulatedPressure > 0
                ? $this->pressureField->pressure($s, $accumulatedPressure)
                : $this->pressureField->contradictionIndex($s);
            $zone = null;
            $cascadeSeverity = null;
        }

        // Collapse Function (đạo rạn nứt): explicit phase transition trigger
        $collapseCondition = $this->collapseTrigger($s);

        $shouldCollapse = $collapseCondition || $pressure > 0.9;

        // Reorganization possible: entropy cao, innovation chưa quá thấp
        $canReorganize = $entropy > $this->entropyForReorganization
            && $innovation > 0.05
            && $resourceFlow > 0.02;

        // Pressure release via event: when can_reorganize, innovation dissipates some pressure
        if ($canReorganize) {
            $release = $this->pressureField->releaseRate($innovation);
            $pressure = max(0.0, $pressure - $release);
            if ($profile !== null) {
                $zone = $this->zoneFromPressure($pressure);
                $cascadeSeverity = $zone === self::ZONE_CRITICAL ? $pressure * $pressure * $pressure : 0.0;
            }
        }

        // Distance to critical (0 = at threshold, 1 = far)
        $distanceToCritical = 1.0 - min(1.0,
            ($contradiction / $this->contradictionThreshold) * 0.4
            + ((1 - $innovation) / (1 - $this->innovationMinThreshold)) * 0.3
            + ((1 - $resourceFlow) / (1 - $this->resourceFlowThreshold)) * 0.3
        );

        $phase = self::PHASE_STABLE;
        if ($shouldCollapse) {
            $phase = self::PHASE_COLLAPSE_IMMINENT;
        } elseif ($collapseCondition) {
            $phase = self::PHASE_CRITICAL;
        } elseif ($canReorganize) {
            $phase = self::PHASE_REORGANIZATION_POSSIBLE;
        }

        $out = [
            'phase' => $phase,
            'distance_to_critical' => max(0, min(1, $distanceToCritical)),
            'should_collapse' => $shouldCollapse,
            'can_reorganize' => $canReorganize,
            'contradiction_index' => $contradiction,
            'pressure' => $pressure,
        ];
        if ($zone !== null) {
            $out['zone'] = $zone;
        }
        if ($cascadeSeverity !== null) {
            $out['cascade_severity'] = $cascadeSeverity;
        }
        return $out;
    }

    /**
     * Collapse Function: trigger structural fracture when
     * contradiction_index > threshold AND innovation_rate < min AND resource_flow < threshold.
     */
    public function collapseTrigger(WorldStateVector $s): bool
    {
        $contradiction = $this->pressureField->contradictionIndex($s);
        $innovation = $s->getInnovation();
        $resourceFlow = $s->getResourceStock();

        return $contradiction > self::COLLAPSE_CONTRADICTION_THRESHOLD
            && $innovation < self::COLLAPSE_INNOVATION_MIN
            && $resourceFlow < self::COLLAPSE_RESOURCE_FLOW_THRESHOLD;
    }

    public function zoneFromPressure(float $pressure): string
    {
        if ($pressure < 0.4) {
            return self::ZONE_STABILITY;
        }
        if ($pressure < 0.75) {
            return self::ZONE_METASTABLE;
        }
        return self::ZONE_CRITICAL;
    }
}
