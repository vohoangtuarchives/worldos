<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\Services;

use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;

/**
 * EmergencyInterventionService — god-mode interventions.
 *
 * 4 emergency actions:
 *   1. injectEntropyShock()        — spike entropy by configurable amount
 *   2. reduceRigidityGlobally()    — lower all attractor rigidity thresholds
 *   3. forceCollapse()             — trigger immediate CTI threshold breach
 *   4. disableEmergentArchetypes() — temporarily block new archetype creation
 *
 * Every intervention is logged with full parameter capture.
 * Interventions return modified WorldSnapshot — caller must persist.
 */
class EmergencyInterventionService
{
    /** @var array<array> */
    private array $interventionLog = [];

    private bool $emergentArchetypesDisabled = false;

    /**
     * Inject entropy shock — spike entropy to destabilize locked systems.
     *
     * @param WorldSnapshot $snapshot Current state
     * @param float $shockMagnitude Entropy increase (0.05 to 0.3)
     * @return WorldSnapshot Modified snapshot
     */
    public function injectEntropyShock(WorldSnapshot $snapshot, float $shockMagnitude = 0.15): WorldSnapshot
    {
        $shockMagnitude = max(0.05, min(0.3, $shockMagnitude));

        $cosmic = $snapshot->cosmic;
        $newEntropy = min(1.0, $cosmic->entropy + $shockMagnitude);
        $newStrain = min(1.0, $cosmic->strain + $shockMagnitude * 0.5);

        $newCosmic = new CosmicState(
            entropy: round($newEntropy, 6),
            energy: $cosmic->energy,
            causality: $cosmic->causality,
            strain: round($newStrain, 6),
            stability: round(max(0.0, $cosmic->stability - $shockMagnitude * 0.3), 6),
            currentAttractor: $cosmic->currentAttractor,
            year: $cosmic->year,
        );

        $this->log('ENTROPY_SHOCK', $snapshot->year, [
            'magnitude' => $shockMagnitude,
            'entropy_before' => $cosmic->entropy,
            'entropy_after' => $newCosmic->entropy,
            'strain_before' => $cosmic->strain,
            'strain_after' => $newCosmic->strain,
        ]);

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $snapshot->environment,
            civilization: $snapshot->civilization,
            year: $snapshot->year,
        );
    }

    /**
     * Reduce rigidity globally — makes all attractors more flexible.
     * This is applied as a stability reduction (proxy for rigidity).
     *
     * @param WorldSnapshot $snapshot Current state
     * @param float $reduction Rigidity reduction factor (0.05 to 0.2)
     * @return WorldSnapshot Modified snapshot
     */
    public function reduceRigidityGlobally(WorldSnapshot $snapshot, float $reduction = 0.1): WorldSnapshot
    {
        $reduction = max(0.05, min(0.2, $reduction));

        $cosmic = $snapshot->cosmic;

        // Reducing rigidity → increasing flexibility → slight entropy increase + strain decrease
        $newCosmic = new CosmicState(
            entropy: round(min(1.0, $cosmic->entropy + $reduction * 0.2), 6),
            energy: $cosmic->energy,
            causality: $cosmic->causality,
            strain: round(max(0.0, $cosmic->strain - $reduction * 0.3), 6),
            stability: $cosmic->stability,
            currentAttractor: $cosmic->currentAttractor,
            year: $cosmic->year,
        );

        $this->log('REDUCE_RIGIDITY', $snapshot->year, [
            'reduction_factor' => $reduction,
            'strain_before' => $cosmic->strain,
            'strain_after' => $newCosmic->strain,
        ]);

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $snapshot->environment,
            civilization: $snapshot->civilization,
            year: $snapshot->year,
        );
    }

    /**
     * Force collapse — trigger immediate CTI threshold breach.
     * This forces the system into a collapse/rebirth cycle.
     *
     * @param WorldSnapshot $snapshot Current state
     * @return WorldSnapshot Modified snapshot with high strain + entropy
     */
    public function forceCollapse(WorldSnapshot $snapshot): WorldSnapshot
    {
        $cosmic = $snapshot->cosmic;

        $newCosmic = new CosmicState(
            entropy: round(min(1.0, $cosmic->entropy + 0.3), 6),
            energy: round(max(0.0, $cosmic->energy - 0.2), 6),
            causality: $cosmic->causality,
            strain: round(min(1.0, $cosmic->strain + 0.4), 6),
            stability: round(max(0.0, $cosmic->stability - 0.4), 6),
            currentAttractor: $cosmic->currentAttractor,
            year: $cosmic->year,
        );

        $this->log('FORCE_COLLAPSE', $snapshot->year, [
            'entropy_before' => $cosmic->entropy,
            'entropy_after' => $newCosmic->entropy,
            'stability_before' => $cosmic->stability,
            'stability_after' => $newCosmic->stability,
            'strain_before' => $cosmic->strain,
            'strain_after' => $newCosmic->strain,
        ]);

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $snapshot->environment,
            civilization: $snapshot->civilization,
            year: $snapshot->year,
        );
    }

    /**
     * Toggle emergent archetype creation.
     *
     * @param bool $disabled True to disable, false to re-enable
     */
    public function setEmergentArchetypesDisabled(bool $disabled, int $epoch = 0): void
    {
        $this->emergentArchetypesDisabled = $disabled;
        $this->log(
            $disabled ? 'DISABLE_EMERGENT' : 'ENABLE_EMERGENT',
            $epoch,
            ['status' => $disabled ? 'disabled' : 'enabled'],
        );
    }

    /**
     * Check if emergent archetypes are disabled.
     */
    public function areEmergentArchetypesDisabled(): bool
    {
        return $this->emergentArchetypesDisabled;
    }

    /**
     * Get full intervention log.
     */
    public function getInterventionLog(): array
    {
        return $this->interventionLog;
    }

    /**
     * Get count of interventions.
     */
    public function getInterventionCount(): int
    {
        return count($this->interventionLog);
    }

    private function log(string $type, int $epoch, array $parameters): void
    {
        $this->interventionLog[] = [
            'type' => $type,
            'epoch' => $epoch,
            'parameters' => $parameters,
            'timestamp' => microtime(true),
        ];
    }

    public function toArray(): array
    {
        return [
            'emergent_archetypes_disabled' => $this->emergentArchetypesDisabled,
            'intervention_log' => $this->interventionLog,
            'intervention_count' => count($this->interventionLog),
        ];
    }
}
