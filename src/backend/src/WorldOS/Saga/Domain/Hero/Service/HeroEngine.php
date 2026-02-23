<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\Service;

use WorldOS\Saga\Domain\Hero\ValueObject\HeroCouplingMatrix;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroStateVector;
use WorldOS\Saga\Domain\Hero\ValueObject\UniverseProjectionMatrix;
use WorldOS\Simulation\Domain\Engine\ValueObject\AnomalyEvent;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * HeroEngine — The mathematical core of the Saga layer.
 *
 * It operates as a Micro-Dynamical System acting upon the HeroStateVector (8D).
 * The formula is: H(t+1) = Logistic( A_h * H(t) + P * U(t) + N(t) )
 * 
 * - A_h: Internal Coupling Matrix (Hero's psychological feedback loops)
 * - P: Universe Projection Matrix (Macro forces pressing on micro state)
 * - U(t): 17D Universe StateVector
 * - N(t): Anomaly / Shock Vector
 *
 * Fully deterministic. No if-else state-machine branches for phases.
 */
final class HeroEngine
{
    private readonly HeroCouplingMatrix $internalCoupling;
    private readonly UniverseProjectionMatrix $projectionMatrix;

    public function __construct(
        ?HeroCouplingMatrix $internalCoupling = null,
        ?UniverseProjectionMatrix $projectionMatrix = null
    ) {
        $this->internalCoupling = $internalCoupling ?? HeroCouplingMatrix::createBaseline();
        $this->projectionMatrix = $projectionMatrix ?? new UniverseProjectionMatrix();
    }

    /**
     * Evolve the Hero's state by one tick based on Universe pressure.
     *
     * @param HeroProfile    $profile     Immutable DNA
     * @param HeroState      $current     Current Hero State (8D Vector wrapper)
     * @param StateVector    $universeState Current Universe State (17D Vector)
     * @param AnomalyEvent[] $anomalies     Universe occurrences right now
     *
     * @return HeroState The next immutable Hero state.
     */
    public function evolve(
        HeroProfile $profile,
        HeroState   $current,
        StateVector $universeState,
        array       $anomalies
    ): HeroState {
        // 1. Compute H_internal = A_h * H(t)
        // Resolves the internal psychological feedback loop.
        $internalEvolution = $this->internalCoupling->multiply($current->getVector());

        // 2. Compute H_forcing = P * U(t)
        // Resolves the exact macro pressure acting upon the hero archetype.
        $macroForcing = $this->projectionMatrix->project($universeState, $profile);

        // 3. Compute H_shock = N(t)
        // External sudden events/anomalies impacting the hero immediately.
        $shockVector = $this->buildAnomalyShockVector($profile, $anomalies);

        // 4. Combine linearly: H_next_raw = H_internal + H_forcing + H_shock
        $nextRaw = $internalEvolution
            ->add($macroForcing)
            ->add($shockVector);

        // 5. Apply non-linear bounding (Logistic / Sigmoid mapping)
        // Keeps the resulting state vector inside [0, 1] without hard clamping,
        // allowing smooth asymptotic approach to absolute extremes.
        $finalVector = $nextRaw->logisticBound();

        // 6. Return new wrapped HeroState
        return HeroState::restore($finalVector);
    }

    /**
     * Builds the Anomaly Shock Vector N(t) which adds direct intense bursts to specific 8D dimensions.
     */
    private function buildAnomalyShockVector(HeroProfile $profile, array $anomalies): HeroStateVector
    {
        $components = [
            HeroStateVector::DIM_STRESS      => 0.0,
            HeroStateVector::DIM_CONVICTION  => 0.0,
            HeroStateVector::DIM_RESILIENCE  => 0.0,
            HeroStateVector::DIM_ADAPTATION  => 0.0,
            HeroStateVector::DIM_FEAR        => 0.0,
            HeroStateVector::DIM_CLARITY     => 0.0,
            HeroStateVector::DIM_EGO         => 0.0,
            HeroStateVector::DIM_TRAUMA      => 0.0,
        ];

        $domDim = $profile->getDominantDimension();

        foreach ($anomalies as $anomaly) {
            // Raw intensity mapping (highly situational, anomaly triggers direct psychological shock)
            
            // Anomalies always cause some stress and fear
            $components[HeroStateVector::DIM_STRESS] += $anomaly->intensity * 0.5;
            $components[HeroStateVector::DIM_FEAR]   += $anomaly->intensity * 0.3;
            $components[HeroStateVector::DIM_TRAUMA] += $anomaly->intensity * 0.2; // Wounds stack up

            if ($anomaly->dimension === $domDim) {
                // Dominant anomaly shock increases clarity and conviction heavily
                $components[HeroStateVector::DIM_CONVICTION] += $anomaly->intensity * 0.6;
                $components[HeroStateVector::DIM_CLARITY]    += $anomaly->intensity * 0.4;
            } else {
                // Off-dimension shock creates confusion/adaptation demand
                $components[HeroStateVector::DIM_CLARITY]    -= $anomaly->intensity * 0.2;
                $components[HeroStateVector::DIM_ADAPTATION] += $anomaly->intensity * 0.3;
            }
        }

        return HeroStateVector::createRaw($components);
    }
}
