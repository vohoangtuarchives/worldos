<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Saga\Domain\Attractor\Service;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Attractor\Service\AttractorEngine;
use WorldOS\Saga\Domain\Attractor\ValueObject\AttractorType;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroCyclePhase;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroStateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

final class AttractorEngineTest extends TestCase
{
    private AttractorEngine $engine;
    private HeroProfile $profile;

    protected function setUp(): void
    {
        $this->engine  = new AttractorEngine();
        $this->profile = HeroProfile::create(StateVector::DIMENSION_TRANSCENDENCE, 0.8);
    }

    public function test_evaluates_collapse_ending_when_universe_and_hero_fail(): void
    {
        // High entropy, high tension
        $universe = StateVector::genesis()
            ->withDimension(StateVector::DIMENSION_ENTROPY, 0.9)
            ->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.9);

        $stateArr = HeroStateVector::genesis($this->profile)->toArray();
        $stateArr[HeroStateVector::DIM_STRESS] = 1.0;
        $hero = HeroState::restore(HeroStateVector::fromArray($stateArr));

        $result = $this->engine->evaluate($universe, $hero, StateVector::DIMENSION_TRANSCENDENCE);

        $this->assertSame(AttractorType::COLLAPSE, $result->getType());
        $this->assertGreaterThan($result->getScoreFor(AttractorType::REWRITE), $result->getScoreFor(AttractorType::COLLAPSE));
    }

    public function test_evaluates_convergence_ending_in_harmonious_state(): void
    {
        // Low entropy, low tension = High stability
        $universe = StateVector::genesis()
            ->withDimension(StateVector::DIMENSION_ENTROPY, 0.1)
            ->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.1);

        $stateArr = HeroStateVector::genesis($this->profile)->toArray();
        $stateArr[HeroStateVector::DIM_STRESS] = 0.0;
        $stateArr[HeroStateVector::DIM_CONVICTION] = 0.5;
        $hero = HeroState::restore(HeroStateVector::fromArray($stateArr));

        $result = $this->engine->evaluate($universe, $hero, StateVector::DIMENSION_TRANSCENDENCE);

        $this->assertSame(AttractorType::CONVERGENCE, $result->getType());
    }

    public function test_evaluates_rewrite_ending_with_unstable_world_and_transcendent_hero(): void
    {
        // High entropy/tension = Low stability.
        // Also the hero's dimension is somewhat present, so they are not completely suppressed (stops Escape from winning).
        $universe = StateVector::genesis()
            ->withDimension(StateVector::DIMENSION_ENTROPY, 0.8)
            ->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.8)
            ->withDimension(StateVector::DIMENSION_TRANSCENDENCE, 0.8);

        // Hero is piercing through limits (Breakthrough equivalent)
        $stateArr = HeroStateVector::genesis($this->profile)->toArray();
        $stateArr[HeroStateVector::DIM_STRESS] = 0.0;
        $stateArr[HeroStateVector::DIM_CONVICTION] = 1.0;
        $hero = HeroState::restore(HeroStateVector::fromArray($stateArr));

        $result = $this->engine->evaluate($universe, $hero, StateVector::DIMENSION_TRANSCENDENCE);

        $this->assertSame(AttractorType::REWRITE, $result->getType());
    }

    public function test_evaluates_escape_ending_when_world_rejects_hero(): void
    {
        // High tension, and the hero's dimension (transcendence) is completely suppressed in the universe (0.1)
        $universe = StateVector::genesis()
            ->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.9)
            ->withDimension(StateVector::DIMENSION_ENTROPY, 0.4)
            ->withDimension(StateVector::DIMENSION_TRANSCENDENCE, 0.1); 

        // Hero is highly convicted but highly stressed
        $stateArr = HeroStateVector::genesis($this->profile)->toArray();
        $stateArr[HeroStateVector::DIM_STRESS] = 0.8;
        $stateArr[HeroStateVector::DIM_CONVICTION] = 0.9;
        $hero = HeroState::restore(HeroStateVector::fromArray($stateArr));

        $result = $this->engine->evaluate($universe, $hero, StateVector::DIMENSION_TRANSCENDENCE);

        $this->assertSame(AttractorType::ESCAPE, $result->getType());
    }
}
