<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Engine;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Application\Evolution\Dynamics\DriftField;
use WorldOS\Evolution\Domain\Legacy\EvolutionContext;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\EvolutionResult;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\VectorForce;

/**
 * VectorDynamicsEngine - Single step of evolution: state + context -> next state, net force, optional branch.
 * No object evolves itself; only this engine computes next state.
 */
final class VectorDynamicsEngine
{
    private const DT = 1.0;

    public function __construct(
        private readonly DriftField $driftField,
        private readonly ?InfluenceAggregatorInterface $influenceAggregator = null,
        private readonly ?BifurcationAnalyzerInterface $bifurcationAnalyzer = null
    ) {
    }

    public function step(WorldStateVector $state, EvolutionContext $context, ?WorldStateVector $prevState = null): EvolutionResult
    {
        // 1. Intrinsic drift
        $fIntrinsic = $this->driftField->compute($state);

        // 2. Collect influence forces (if aggregator present)
        $fInfluence = $this->influenceAggregator !== null
            ? $this->influenceAggregator->aggregate($state, $context)
            : VectorForce::zero();

        // 3. Aggregate: netForce = intrinsic + influence (sensitivity matrix applied in aggregator if present)
        $netForce = $this->addForces($fIntrinsic, $fInfluence);

        // 4. Non-linear integration
        $nextState = $this->integrate($state, $netForce);

        // 5. Bifurcation analysis (curvature, divergence)
        $branch = null;
        if ($this->bifurcationAnalyzer !== null && $prevState !== null) {
            $branch = $this->bifurcationAnalyzer->analyze($nextState, $prevState, $netForce, $context);
        }

        return new EvolutionResult($nextState, $netForce, $branch);
    }

    private function addForces(VectorForce $a, VectorForce $b): VectorForce
    {
        return $a->add($b);
    }

    /**
     * Non-linear integration: next[i] = clamp(state[i] + tanh(netForce[i] * dt), 0, 1).
     * Cross-dimensional terms can be added for stronger emergence.
     */
    private function integrate(WorldStateVector $state, VectorForce $netForce): WorldStateVector
    {
        $dims = WorldStateVector::dimensions();
        $components = [];

        foreach ($dims as $dim) {
            $s = $state->get($dim);
            $f = $netForce->get($dim);
            // Logistic-style: tanh keeps small steps smooth, clamp to [0,1]
            $delta = tanh($f * self::DT);
            $next = $s + $delta;
            // Optional cross-term: e.g. order * cohesion dampens entropy (example)
            if ($dim === WorldStateVector::DIMENSION_ENTROPY) {
                $order = $state->get(WorldStateVector::DIMENSION_ORDER);
                $cohesion = $state->get(WorldStateVector::DIMENSION_COHESION);
                $next -= 0.02 * $order * $cohesion * $s; // dampen entropy when order+cohesion high
            }
            $components[$dim] = max(0.0, min(1.0, (float) $next));
        }

        return new WorldStateVector($components);
    }
}
