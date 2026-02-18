<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Kernel;

use App\Domains\Cosmology\Entities\Universe as CosmologyUniverse;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Evolution\ArcPhase;
use App\Domains\Saga\DTO\ShockParams;
use App\Domains\Cosmology\Evolution\PresetDescriptor;
use App\Domains\Cosmology\Evolution\RegimeModifier;
use App\Domains\Cosmology\Services\BasePhysicsEngine;
use App\Domains\Cosmology\Services\StructuralMutationEngine;
use App\Domains\Evolution\Engine\VectorDynamicsEngine;
use App\Domains\Evolution\EvolutionContext;
use App\Domains\Evolution\ValueObjects\BranchEvent;
use App\Models\World;

/**
 * WorldEvolutionKernel - Evolves world by stepping physics (BasePhysicsEngine when injected) or VectorDynamicsEngine; load/save at boundary.
 * Flow when using BasePhysicsEngine: basePhysics->evolve(v, preset, regime) → phase signal → collapse/reorganize at World layer.
 */
final class WorldEvolutionKernel
{
    public function __construct(
        private readonly VectorDynamicsEngine $engine,
        private readonly StateLoader $stateLoader,
        private readonly ?BasePhysicsEngine $basePhysics = null,
        private readonly ?StructuralMutationEngine $mutationEngine = null
    ) {
    }

    /**
     * Evolve world by given years. Returns BranchEvent if bifurcation occurred (caller should handle onWorldComplete).
     * When BasePhysicsEngine is injected, uses it for physics step and applies collapse/reorganize at World layer.
     */
    public function evolve(World $world, int $years = 1): ?BranchEvent
    {
        $state = $this->stateLoader->loadVector($world);
        $prevState = null;

        if ($this->basePhysics !== null) {
            return $this->evolveWithBasePhysics($world, $state, $years);
        }

        for ($i = 0; $i < $years; $i++) {
            $context = EvolutionContext::fromWorld($world, (int) ($world->current_time ?? 0) + $i);
            $result = $this->engine->step($state, $context, $prevState);

            $prevState = $state;
            $state = $result->nextState;

            if ($result->branch !== null) {
                $world->current_time = (int) ($world->current_time ?? 0) + $i + 1;
                $world->entropy = $state->getEntropy();
                $world->save();
                $this->stateLoader->saveVector($world, $state);
                return $result->branch;
            }
        }

        $world->current_time = (int) ($world->current_time ?? 0) + $years;
        $world->entropy = $state->getEntropy();
        $world->save();
        $this->stateLoader->saveVector($world, $state);

        return null;
    }

    private function evolveWithBasePhysics(World $world, WorldStateVector $state, int $years): ?BranchEvent
    {
        $preset = PresetDescriptor::fromWorld($world);
        $regime = RegimeModifier::forPhase(ArcPhase::EXPANSION);

        for ($i = 0; $i < $years; $i++) {
            $state = $this->basePhysics->evolve($state, $preset, $regime);
            $signal = $this->basePhysics->getLastPhaseSignal();
            if ($signal !== null && $signal->shouldCollapse && $this->mutationEngine !== null) {
                $state = $this->mutationEngine->mutate($state, $signal->pressure);
            }
        }

        $world->current_time = (int) ($world->current_time ?? 0) + $years;
        $world->entropy = $state->getEntropy();
        $world->save();
        $this->stateLoader->saveVector($world, $state);

        return null;
    }

    /**
     * One tick for a Universe (runtime instance of a World). Uses BasePhysicsEngine when available.
     * Phase 4.2: Optional ShockParams applied after physics to perturb state (Saga mode).
     * Caller must persist universe via CosmologyRepository::save() after this.
     */
    public function tickUniverse(World $world, CosmologyUniverse $universe, ?ShockParams $shock = null): void
    {
        if ($this->basePhysics !== null) {
            $preset = PresetDescriptor::fromWorld($world);
            $regime = RegimeModifier::forPhase(ArcPhase::EXPANSION);
            $state = $this->basePhysics->evolve($universe->getState(), $preset, $regime);
            $signal = $this->basePhysics->getLastPhaseSignal();
            if ($signal !== null && $signal->shouldCollapse && $this->mutationEngine !== null) {
                $state = $this->mutationEngine->mutate($state, $signal->pressure);
            }
            if ($shock !== null) {
                $state = $this->applyShockPerturbation($state, $shock);
            }
            $universe->setState($state);
            $universe->setAge($universe->getAge() + 1);
            return;
        }

        throw new \RuntimeException('WorldEvolutionKernel::tickUniverse requires BasePhysicsEngine. Bind BasePhysicsEngine in the container.');
    }

    /**
     * Apply one-off shock perturbation to state (Saga mode: military, resource, ideology, tech).
     */
    private function applyShockPerturbation(WorldStateVector $state, ShockParams $shock): WorldStateVector
    {
        $scale = 0.3 * $shock->magnitude;
        $all = $state->getAll();

        switch ($shock->type) {
            case 'military':
                $all[WorldStateVector::DIMENSION_MILITARY] = $this->clamp01(
                    $all[WorldStateVector::DIMENSION_MILITARY] + $scale
                );
                break;
            case 'resource':
                $all[WorldStateVector::DIMENSION_ENTROPY] = $this->clamp01(
                    $all[WorldStateVector::DIMENSION_ENTROPY] + $scale
                );
                $all[WorldStateVector::DIMENSION_RESOURCE_STOCK] = $this->clamp01(
                    ($all[WorldStateVector::DIMENSION_RESOURCE_STOCK] ?? 0.5) - $scale
                );
                break;
            case 'ideology':
                $all[WorldStateVector::DIMENSION_ENTROPY] = $this->clamp01(
                    $all[WorldStateVector::DIMENSION_ENTROPY] + $scale * 0.5
                );
                $all[WorldStateVector::DIMENSION_COHESION] = $this->clamp01(
                    $all[WorldStateVector::DIMENSION_COHESION] - $scale
                );
                break;
            case 'tech':
                $all[WorldStateVector::DIMENSION_INNOVATION] = $this->clamp01(
                    $all[WorldStateVector::DIMENSION_INNOVATION] + $scale
                );
                break;
            default:
                break;
        }

        return WorldStateVector::fromArray($all);
    }

    private function clamp01(float $v): float
    {
        return max(0.0, min(1.0, $v));
    }
}
