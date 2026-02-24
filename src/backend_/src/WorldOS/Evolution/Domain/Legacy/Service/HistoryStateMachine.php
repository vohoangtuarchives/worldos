<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldField;
use WorldOS\Evolution\Domain\Legacy\ValueObject\EnvironmentState;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationResidual;
use WorldOS\Evolution\Domain\Legacy\Enum\CivilizationPhase;
use WorldOS\Evolution\Domain\Legacy\Enum\CivilizationLifecycleState;
use WorldOS\Evolution\Domain\Legacy\Enum\PowerStage;

class HistoryStateMachine
{
    public const STATE_GROWTH = 'GROWTH';
    public const STATE_STRESS = 'STRESS';
    public const STATE_CRISIS = 'CRISIS';
    public const STATE_COLLAPSE = 'COLLAPSE';
    public const STATE_REGENERATION = 'REGENERATION';

    public function determineMacroState(CivilizationSnapshot $civ): string
    {
        if ($civ->internalEntropy > 0.95 || $civ->energy < 0.05) {
            return self::STATE_COLLAPSE;
        }

        if ($civ->internalEntropy > 0.8 || $civ->stability < 0.2) {
            return self::STATE_CRISIS;
        }

        if ($civ->stability < 0.5 || $civ->inequality > 0.7) {
            return self::STATE_STRESS;
        }

        if ($civ->prosperity > 0.6 && $civ->stability > 0.6) {
            return self::STATE_GROWTH;
        }

        return self::STATE_REGENERATION;
    }

    /**
     * Determine the biological/ontological lifecycle state.
     */
    public function determineLifecycleState(CivilizationSnapshot $civ): CivilizationLifecycleState
    {
        // Absolute extinction: nothing left to even be called a civilization
        // We want civilizations to survive highly entropic states (0.80+) without instantly dying.
        // It must hit absolute zero resilience AND absolute max entropy or zero stability.
        if ($civ->resilience <= 0.0001 && ($civ->stability <= 0.001 || $civ->internalEntropy >= 0.999)) {
            echo "EXTINCT TRIGGERED -> resilience: {$civ->resilience}, stability: {$civ->stability}, entropy: {$civ->internalEntropy}\n";
            return CivilizationLifecycleState::EXTINCT;
        }

        $macro = $this->determineMacroState($civ);

        return match ($macro) {
            self::STATE_COLLAPSE => CivilizationLifecycleState::COLLAPSE,
            self::STATE_CRISIS => CivilizationLifecycleState::CRISIS,
            self::STATE_STRESS => CivilizationLifecycleState::STRESS,
            self::STATE_GROWTH => CivilizationLifecycleState::GROWTH,
            default => CivilizationLifecycleState::EMERGENCE,
        };
    }

    /**
     * Check if the current field and environment conditions are fertile enough
     * for a new civilization to emerge.
     */
    public function checkEmergence(WorldField $field, EnvironmentState $env, string $seed): bool
    {
        // 1. Environmental Guard: World must be habitable
        if ($env->getHabitability() < 0.2) {
            return false;
        }

        // 2. Space of Possibility: High magic or high tech density increases emergence probability
        $magic = $field->influenceVector['magic'] ?? 0.0;
        $tech = $field->influenceVector['tech'] ?? 0.0;
        $chaos = $field->influenceVector['chaos'] ?? 0.0;
        
        // Probability baseline
        $p = 0.05 + ($magic * 0.1) + ($tech * 0.1) + ($chaos * 0.05);
        
        $rng = hexdec(substr(md5($seed . '_emergence'), 0, 8)) / 0xffffffff;
        
        return $rng < $p;
    }

    /**
     * Spawn a new civilization from the "Ash" state, influenced by the current Field, with optional memory.
     */
    public function spawnNewCivilization(WorldField $field, EnvironmentState $env, int $year, ?CivilizationResidual $previousResidual = null): CivilizationSnapshot
    {
        $magic = $field->influenceVector['magic'] ?? 0.0;
        $tech = $field->influenceVector['tech'] ?? 0.0;
        
        $inheritedTrauma = $previousResidual ? $previousResidual->cumulativeTrauma : 0.0;
        $newResidual = new CivilizationResidual();
        $newResidual->cumulativeTrauma = $inheritedTrauma + 0.5; // Apocalyptic event leaves a HUGE scar
        
        // Bias technological level based on Tech field
        // Bias cultural energy based on Magic field
        return new CivilizationSnapshot(
            culturalEnergy: min(1.0, 0.1 + $magic * 0.5),
            spiritualCohesion: 0.5,
            technologicalLevel: min(1.0, 0.05 + $tech * 0.3),
            stability: 0.8 - min(0.6, $inheritedTrauma * 0.1), // Starts less stable if history is traumatized
            prosperity: 0.3,
            militaryPressure: 0.0,
            externalThreat: 0.0,
            internalEntropy: 0.1 + min(0.6, $inheritedTrauma * 0.2), // Traumatized world has higher baseline entropy
            legitimacy: 0.7,
            eliteCohesion: 0.8,
            inequality: 0.1,
            resonanceAccumulator: 0.0,
            resilience: 0.5,
            year: $year,
            yearsInPhase: 0,
            historyPhase: CivilizationPhase::EMERGENCE,
            powerStage: PowerStage::STAGE_0_MUNDANE,
            fieldCurvature: 0.0,
            energy: 1.0,
            socialClasses: [], // Will be populated by SocialDynamicsService
            residual: $newResidual,
            lifecycleState: CivilizationLifecycleState::EMERGENCE,
            narrativeTension: 0.5,
            shortWaveTension: 0.5, // Start with high tension (Mad Max phase)
            longWaveTension: $previousResidual ? min(1.0, 0.3 + $inheritedTrauma * 0.1) : 0.0 // Carry over some long wave tension
        );
    }

    /**
     * Perform a Hard Reset on the civilization snapshot if it collapses.
     */
    public function applyCollapse(CivilizationSnapshot $civ): CivilizationSnapshot
    {
        // ... (existing code)
        // Reset 10 dimensions to "Ash" state
        $data = $civ->toArray();
        $currentTrauma = $data['residual']['cumulative_trauma'] ?? 0.0;
        $newTrauma = $currentTrauma + 0.3; // Adding a massive collapse scar
        
        if (!isset($data['residual'])) $data['residual'] = [];
        $data['residual']['cumulative_trauma'] = $newTrauma;

        $data['stability'] = max(0.01, 0.2 - $currentTrauma * 0.05); // Ruins are less stable if history is bad
        $data['prosperity'] = 0.3 + 0.2 * (mt_rand() / mt_getrandmax()); // Random prosperity boost in the new era
        $data['internal_entropy'] = min(1.0, $civ->internalEntropy + 0.05); // Entropy baseline permanently increases
        $data['inequality'] = 0.2; // Everyone is equal in the ruins
        $data['technological_level'] = $civ->technologicalLevel * 0.5; // Tech loss
        $data['cultural_energy'] = 0.1;
        $data['military_pressure'] = 0.0;
        $data['legitimacy'] = 0.1;
        $data['energy'] = 1.0; // Energy reset for new growth
        $data['history_phase'] = CivilizationPhase::FRAGMENTATION->value;
        $data['lifecycle_state'] = CivilizationLifecycleState::EMERGENCE->value;
        
        // Reset short wave tension to a chaotic starting level, but keep long wave tension from previous cycle
        $data['short_wave_tension'] = 0.4;

        return CivilizationSnapshot::fromArray($data);
    }
}
