<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;
use App\Models\UniverseModel;

/**
 * EmergencyInterventionService — god-mode interventions.
 *
 * WorldOS v3: V3 methods operate on UniverseModel (state_vector) instead of WorldSnapshot.
 * Legacy WorldSnapshot methods kept for WriterConsole backward compatibility.
 *
 * Every intervention is logged with full parameter capture.
 */
class EmergencyInterventionService
{
    /** @var array<array> */
    private array $interventionLog = [];

    private bool $emergentArchetypesDisabled = false;

    // =====================================================================
    // V3 Methods — operate on UniverseModel (state_vector JSON)
    // =====================================================================

    /**
     * V3: Inject entropy shock directly on a Universe's state_vector.
     */
    public function injectEntropyShockV3(UniverseModel $universe, float $shockMagnitude = 0.15): UniverseModel
    {
        $shockMagnitude = max(0.05, min(0.3, $shockMagnitude));
        $sv = $universe->state_vector ?? [];

        $oldEntropy = (float) ($sv['entropy'] ?? 0.0);
        $oldOrder = (float) ($sv['order'] ?? 1.0);
        $sv['entropy'] = round(min(1.0, $oldEntropy + $shockMagnitude), 6);
        $sv['order'] = round(max(0.0, $oldOrder - $shockMagnitude * 0.3), 6);

        $universe->update([
            'state_vector' => $sv,
            'entropy' => $sv['entropy'],
        ]);

        $this->log('ENTROPY_SHOCK_V3', (int) ($universe->age ?? 0), [
            'universe_id' => $universe->id,
            'magnitude' => $shockMagnitude,
            'entropy_before' => $oldEntropy,
            'entropy_after' => $sv['entropy'],
        ]);

        return $universe->fresh();
    }

    /**
     * V3: Reduce rigidity on a Universe's state_vector.
     */
    public function reduceRigidityV3(UniverseModel $universe, float $reduction = 0.1): UniverseModel
    {
        $reduction = max(0.05, min(0.2, $reduction));
        $sv = $universe->state_vector ?? [];

        $oldEntropy = (float) ($sv['entropy'] ?? 0.0);
        $sv['entropy'] = round(min(1.0, $oldEntropy + $reduction * 0.2), 6);
        // Reduce tech_progress slightly (proxy for institutional rigidity)
        if (isset($sv['tech_progress'])) {
            $sv['tech_progress'] = round(max(0.0, (float) $sv['tech_progress'] - $reduction * 0.15), 6);
        }

        $universe->update([
            'state_vector' => $sv,
            'entropy' => $sv['entropy'],
        ]);

        $this->log('REDUCE_RIGIDITY_V3', (int) ($universe->age ?? 0), [
            'universe_id' => $universe->id,
            'reduction_factor' => $reduction,
        ]);

        return $universe->fresh();
    }

    /**
     * V3: Force collapse on a Universe by spiking entropy and draining order/stability.
     */
    public function forceCollapseV3(UniverseModel $universe): UniverseModel
    {
        $sv = $universe->state_vector ?? [];

        $sv['entropy'] = round(min(1.0, ((float) ($sv['entropy'] ?? 0.0)) + 0.4), 6);
        $sv['order'] = round(max(0.0, ((float) ($sv['order'] ?? 1.0)) - 0.4), 6);
        if (isset($sv['stability'])) {
            $sv['stability'] = round(max(0.0, ((float) $sv['stability']) - 0.4), 6);
        }

        $universe->update([
            'state_vector' => $sv,
            'entropy' => $sv['entropy'],
            'stability_index' => $sv['stability'] ?? null,
            'status' => 'collapsed',
        ]);

        $this->log('FORCE_COLLAPSE_V3', (int) ($universe->age ?? 0), [
            'universe_id' => $universe->id,
            'entropy_after' => $sv['entropy'],
            'order_after' => $sv['order'],
        ]);

        return $universe->fresh();
    }

    // =====================================================================
    // Legacy Methods — operate on WorldSnapshot VO (for WriterConsole compat)
    // =====================================================================

    /**
     * @deprecated V3: Use injectEntropyShockV3() instead.
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
        ]);

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $snapshot->environment,
            civilization: $snapshot->civilization,
            year: $snapshot->year,
        );
    }

    /**
     * @deprecated V3: Use reduceRigidityV3() instead.
     */
    public function reduceRigidityGlobally(WorldSnapshot $snapshot, float $reduction = 0.1): WorldSnapshot
    {
        $reduction = max(0.05, min(0.2, $reduction));
        $cosmic = $snapshot->cosmic;

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
        ]);

        return new WorldSnapshot(
            cosmic: $newCosmic,
            environment: $snapshot->environment,
            civilization: $snapshot->civilization,
            year: $snapshot->year,
        );
    }

    /**
     * @deprecated V3: Use forceCollapseV3() instead.
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
            'entropy_after' => $newCosmic->entropy,
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

    public function areEmergentArchetypesDisabled(): bool
    {
        return $this->emergentArchetypesDisabled;
    }

    public function getInterventionLog(): array
    {
        return $this->interventionLog;
    }

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

