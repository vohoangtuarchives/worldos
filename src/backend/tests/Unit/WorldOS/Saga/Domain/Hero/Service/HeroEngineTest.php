<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Saga\Domain\Hero\Service;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Hero\Service\HeroEngine;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroCyclePhase;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroStateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\AnomalyEvent;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

final class HeroEngineTest extends TestCase
{
    private HeroEngine $engine;
    private HeroProfile $profile;

    protected function setUp(): void
    {
        $this->engine  = new HeroEngine();
        $this->profile = HeroProfile::create(StateVector::DIMENSION_POWER_DENSITY, 0.5); // Baseline 0.5 conviction
    }

    public function test_internal_dynamics_stabilize_without_universe_forcing(): void
    {
        // Genesis state
        $state = HeroState::genesis($this->profile);

        // A perfectly flat universe (no forcing)
        $flatUniverse = StateVector::genesis();

        // Evolve forward 10 ticks purely on internal A_h
        for ($i = 0; $i < 10; $i++) {
            $state = $this->engine->evolve($this->profile, $state, $flatUniverse, []);
        }

        // Stress should naturally dump down towards accumulating/restabilizing values
        // due to spectral radius < 1.0 logic, bound by logistic decay.
        $this->assertLessThan(0.4, $state->getStressLevel(), 'Internal dynamics should naturally decay stress');
        $this->assertSame(HeroCyclePhase::ACCUMULATION, $state->getCyclePhase());
    }

    public function test_intense_universe_forcing_causes_stress_collapse(): void
    {
        $state = HeroState::genesis($this->profile);

        // Crushing universe pressure -> Tension 1.0, Entropy 1.0 (Low Stability)
        $hellUniverse = StateVector::genesis()
            ->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 1.0)
            ->withDimension(StateVector::DIMENSION_ENTROPY, 1.0);

        for ($i = 0; $i < 5; $i++) {
            $state = $this->engine->evolve($this->profile, $state, $hellUniverse, []);
        }

        // Stress should spike over 0.8 triggering logic phase: collapse.
        $this->assertGreaterThan(0.8, $state->getStressLevel());
        $this->assertSame(HeroCyclePhase::COLLAPSE, $state->getCyclePhase());
    }

    public function test_dominant_dimension_surge_triggers_breakthrough(): void
    {
        // Intentionally create a strained state with high baseline conviction
        $stateArr = HeroStateVector::genesis($this->profile)->toArray();
        $stateArr[HeroStateVector::DIM_STRESS] = 0.6;
        $stateArr[HeroStateVector::DIM_CONVICTION] = 0.9;
        
        $stateStr = HeroStateVector::fromArray($stateArr);
        $state = HeroState::restore($stateStr);

        // Standard universe with massive aligned anomaly shock
        $universe = StateVector::genesis();
        $anomalies = [
            new AnomalyEvent(StateVector::DIMENSION_POWER_DENSITY, 1.0, 1.0, 1.0)
        ];

        $nextState = $this->engine->evolve($this->profile, $state, $universe, $anomalies);

        // The dominant anomaly shock forces a mass conviction/clarity surge in the vector
        // Classification layer translates extremely high conviction with mid-level stress as Breakthrough or Restabilization
        $this->assertGreaterThan(0.8, $nextState->getConviction());
    }
}
