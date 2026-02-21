<?php

namespace Tuzy\Application\Cosmology\Services;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Application\Cosmology\Evolution\PresetDescriptor;
use Tuzy\Application\Cosmology\Mathematics\Vector;
use Tuzy\Application\Cosmology\Mathematics\PressureAccumulationField;
use Tuzy\Application\Cosmology\Mathematics\CriticalityDetector;
use Tuzy\Application\Cosmology\Mathematics\InnovationBurst;
use Tuzy\Domain\Cosmology\ValueObject\PhaseSignal;

/**
 * Base Physics Engine — pure dynamics (differentials, feedback, criticality).
 * Collapse/reorganize and StructuralMutationEngine are applied at World layer, not here.
 *
 * 1. Pressure Accumulation Field, Criticality Detector, Innovation Burst (unchanged).
 * 2. evolve() returns next state; when critical, feedback (order/entropy drop) is applied but
 *    structural mutation is NOT applied — caller uses getLastPhaseSignal() and applies mutation if needed.
 */
class BasePhysicsEngine
{
    protected float $entropyInequalityRate = 0.05;
    protected float $entropyTraumaRate = 0.03;
    protected float $entropyStagnationRate = 0.02;
    protected float $entropyDampening = 0.04;
    protected float $orderCohesionRate = 0.04;
    protected float $orderMilitaryRate = 0.01;
    protected float $orderEntropyDecay = 0.05;
    protected float $inequalityAccumulation = 0.01;
    protected float $inequalityEliteGraft = 0.02;
    protected float $inequalityRedistribution = 0.03;
    protected float $traumaWarRate = 0.05;
    protected float $traumaCollapseRate = 0.10;
    protected float $traumaHealingRate = 0.005;
    protected float $resourceConsumption = 0.01;
    protected float $resourceInnovationYield = 0.02;
    protected float $resourceWarCost = 0.05;
    protected float $criticalEntropyThreshold = 0.85;
    protected float $criticalInequalityThreshold = 0.70;
    protected float $tippingPointMultiplier = 2.5;

    protected PressureAccumulationField $pressureField;
    protected CriticalityDetector $criticalityDetector;
    protected InnovationBurst $innovationBurst;

    /** Last assessment (for PhaseSignal / collapse at World layer). */
    private ?array $lastAssessment = null;

    public function __construct(
        ?PressureAccumulationField $pressureField = null,
        ?CriticalityDetector $criticalityDetector = null,
        ?InnovationBurst $innovationBurst = null,
    ) {
        $this->pressureField = $pressureField ?? new PressureAccumulationField();
        $this->criticalityDetector = $criticalityDetector ?? new CriticalityDetector($this->pressureField);
        $this->innovationBurst = $innovationBurst ?? new InnovationBurst();
    }

    /**
     * @param PresetDescriptor|null $preset
     * @param \Tuzy\Application\Cosmology\Evolution\RegimeModifier|null $regime
     */
    public function evolve(WorldStateVector $current, ?PresetDescriptor $preset = null, ?\Tuzy\Application\Cosmology\Evolution\RegimeModifier $regime = null): WorldStateVector
    {
        $d = $this->calculateDifferentials($current, $preset, $regime);
        $intermediate = $current->add($d);
        $next = new WorldStateVector($intermediate->getAll());
        $next = $this->applyFeedbackLoops($next, $preset);
        return $this->clampState($next);
    }

    /** Last criticality assessment (array). Null before first evolve(). */
    public function getLastAssessment(): ?array
    {
        return $this->lastAssessment;
    }

    /** Last assessment as PhaseSignal for World layer (collapse/reorganize). */
    public function getLastPhaseSignal(): ?PhaseSignal
    {
        return $this->lastAssessment !== null ? PhaseSignal::fromAssessment($this->lastAssessment) : null;
    }

    private function coeff(?PresetDescriptor $preset, ?\Tuzy\Application\Cosmology\Evolution\RegimeModifier $regime, string $param, float $fallback): float
    {
        $base = $preset !== null ? $preset->get($param, $fallback) : $fallback;
        $mul = $regime !== null ? $regime->multiplierFor($param) : 1.0;
        return $base * $mul;
    }

    protected function calculateDifferentials(WorldStateVector $s, ?PresetDescriptor $preset = null, ?\Tuzy\Application\Cosmology\Evolution\RegimeModifier $regime = null): Vector
    {
        $d = [];
        $dEntropy = ($this->coeff($preset, $regime, 'entropy_inequality_rate', $this->entropyInequalityRate) * pow($s->getInequality(), 2))
            + ($this->coeff($preset, $regime, 'entropy_trauma_rate', $this->entropyTraumaRate) * $s->getTrauma())
            + ($this->coeff($preset, $regime, 'entropy_stagnation_rate', $this->entropyStagnationRate) * (1.0 - $s->getInnovation()))
            - ($this->coeff($preset, $regime, 'entropy_dampening', $this->entropyDampening) * $s->getCohesion());
        $d[WorldStateVector::DIMENSION_ENTROPY] = $dEntropy;

        $dOrder = ($this->coeff($preset, $regime, 'order_cohesion_rate', $this->orderCohesionRate) * $s->getCohesion())
            + ($this->coeff($preset, $regime, 'order_military_rate', $this->orderMilitaryRate) * $s->getMilitary())
            - ($this->coeff($preset, $regime, 'order_entropy_decay', $this->orderEntropyDecay) * $s->getEntropy());
        $d[WorldStateVector::DIMENSION_ORDER] = $dOrder;

        $graftPressure = $s->getEliteCohesion() * (1.0 - $s->getLegitimacy());
        $dInequality = $this->coeff($preset, $regime, 'inequality_accumulation', $this->inequalityAccumulation)
            + ($this->coeff($preset, $regime, 'inequality_elite_graft', $this->inequalityEliteGraft) * $graftPressure)
            - ($this->coeff($preset, $regime, 'inequality_redistribution', $this->inequalityRedistribution) * $s->getLegitimacy());
        $d[WorldStateVector::DIMENSION_INEQUALITY] = $dInequality;

        $warTrauma = ($s->getMilitary() > 0.6) ? ($s->getMilitary() - 0.6) * $this->coeff($preset, $regime, 'trauma_war_rate', $this->traumaWarRate) : 0;
        $chaosTrauma = ($s->getEntropy() > 0.7) ? ($s->getEntropy() - 0.7) * $this->coeff($preset, $regime, 'trauma_collapse_rate', $this->traumaCollapseRate) : 0;
        $dTrauma = $warTrauma + $chaosTrauma - ($this->coeff($preset, $regime, 'trauma_healing_rate', $this->traumaHealingRate) * $s->getCohesion());
        $d[WorldStateVector::DIMENSION_TRAUMA] = $dTrauma;

        $dElite = (0.01 * $s->getOrder()) + (0.01 * $s->getResourceStock()) - (0.02 * $s->getEntropy());
        $d[WorldStateVector::DIMENSION_ELITE_COHESION] = $dElite;

        $dResource = ($this->coeff($preset, $regime, 'resource_innovation_yield', $this->resourceInnovationYield) * $s->getInnovation())
            - $this->coeff($preset, $regime, 'resource_consumption', $this->resourceConsumption)
            - ($this->coeff($preset, $regime, 'resource_war_cost', $this->resourceWarCost) * $s->getMilitary());
        $d[WorldStateVector::DIMENSION_RESOURCE_STOCK] = $dResource;

        $dMilitary = (0.01 * $s->getInequality()) + (0.01 * $s->getEntropy()) - (0.01 * $s->getCohesion());
        $d[WorldStateVector::DIMENSION_MILITARY] = $dMilitary;
        $d[WorldStateVector::DIMENSION_COHESION] = (0.01 * $s->getOrder()) - (0.02 * $s->getInequality());
        $d[WorldStateVector::DIMENSION_LEGITIMACY] = (0.01 * $s->getCohesion()) - (0.03 * $s->getInequality());

        $baseDInnovation = (0.01 * $s->getOrder()) - (0.01 * $s->getTrauma());
        $d[WorldStateVector::DIMENSION_INNOVATION] = $this->innovationBurst->deltaInnovation($s, $baseDInnovation);

        return new Vector($d);
    }

    /**
     * Apply feedback loops. When should_collapse we apply entropy/order feedback only;
     * structural mutation is NOT applied here — caller uses getLastPhaseSignal() and StructuralMutationEngine.
     */
    protected function applyFeedbackLoops(WorldStateVector $s, ?PresetDescriptor $preset = null): WorldStateVector
    {
        $comp = $s->getAll();
        $criticalEntropy = $this->coeff($preset, null, 'critical_entropy_threshold', $this->criticalEntropyThreshold);
        $criticalInequality = $this->coeff($preset, null, 'critical_inequality_threshold', $this->criticalInequalityThreshold);
        $tippingMultiplier = $this->coeff($preset, null, 'tipping_point_multiplier', $this->tippingPointMultiplier);

        $this->lastAssessment = $this->criticalityDetector->assess($s);

        if ($this->lastAssessment['should_collapse']) {
            // Signal collapse via lastAssessment; do not apply StructuralMutation here.
            // Apply only physics feedback (order/legitimacy drop) so state reflects criticality.
            $pressure = $this->lastAssessment['pressure'] ?? 0.9;
            $collapseForce = $pressure * $tippingMultiplier * 0.5;
            $comp[WorldStateVector::DIMENSION_ORDER] = max(0, $comp[WorldStateVector::DIMENSION_ORDER] - $collapseForce);
            $comp[WorldStateVector::DIMENSION_LEGITIMACY] = max(0, $comp[WorldStateVector::DIMENSION_LEGITIMACY] - $collapseForce);
            $comp[WorldStateVector::DIMENSION_INEQUALITY] = min(1.0, $comp[WorldStateVector::DIMENSION_INEQUALITY] + $collapseForce * 0.5);
        }
        if ($this->lastAssessment['can_reorganize']) {
            $boost = $this->innovationBurst->reorganizationBoost($s, true);
            $comp[WorldStateVector::DIMENSION_INNOVATION] = min(1.0, $comp[WorldStateVector::DIMENSION_INNOVATION] + $boost);
        }

        if ($s->getEntropy() > $criticalEntropy) {
            $collapseForce = ($s->getEntropy() - $criticalEntropy) * $tippingMultiplier;
            $comp[WorldStateVector::DIMENSION_ORDER] -= $collapseForce;
            $comp[WorldStateVector::DIMENSION_LEGITIMACY] -= $collapseForce;
            $comp[WorldStateVector::DIMENSION_INEQUALITY] += $collapseForce * 0.5;
        }

        if ($s->getResourceStock() < 0.1) {
            $comp[WorldStateVector::DIMENSION_MILITARY] *= 0.9;
            $comp[WorldStateVector::DIMENSION_INNOVATION] *= 0.5;
            $comp[WorldStateVector::DIMENSION_TRAUMA] += 0.05;
        }

        if ($s->getInequality() > $criticalInequality && $s->getLegitimacy() < 0.3) {
            $comp[WorldStateVector::DIMENSION_ORDER] -= 0.1;
            $comp[WorldStateVector::DIMENSION_ENTROPY] += 0.05;
            $comp[WorldStateVector::DIMENSION_TRAUMA] += 0.02;
        }

        return new WorldStateVector($comp);
    }

    public function assessCriticality(WorldStateVector $s): array
    {
        return $this->criticalityDetector->assess($s);
    }

    protected function clampState(WorldStateVector $state): WorldStateVector
    {
        $clamped = [];
        foreach ($state->getAll() as $key => $value) {
            $clamped[$key] = max(0.0, min(1.0, $value));
        }
        return new WorldStateVector($clamped);
    }
}
