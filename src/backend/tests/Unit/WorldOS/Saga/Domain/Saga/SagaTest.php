<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Saga\Domain\Saga;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroStateVector;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;
use WorldOS\Saga\Domain\Saga\Entity\Saga;
use WorldOS\Saga\Domain\Saga\ValueObject\LifecyclePhase;
use WorldOS\Saga\Domain\Saga\ValueObject\SagaMetrics;

final class SagaTest extends TestCase
{
    private HeroState $heroState;
    private MythVector $mythVector;

    protected function setUp(): void
    {
        $profile = HeroProfile::create(\WorldOS\Simulation\Domain\Engine\ValueObject\StateVector::DIMENSION_ENTROPY, 0.5);
        $this->heroState = HeroState::genesis($profile);
        $this->mythVector = MythVector::genesis();
    }

    public function test_spawn_creates_saga_in_seed_phase(): void
    {
        $saga = Saga::spawn('saga_1', 'universe_1', $this->heroState, $this->mythVector);

        $this->assertSame('saga_1', $saga->getId());
        $this->assertSame('universe_1', $saga->getUniverseId());
        $this->assertSame(LifecyclePhase::SEED, $saga->getLifecyclePhase());
        $this->assertSame(0, $saga->getChapterCount());
        $this->assertEmpty($saga->getArcHistory());
        $this->assertEquals($this->heroState, $saga->getHeroState());
        $this->assertEquals($this->mythVector, $saga->getMythVector());
    }

    public function test_evolve_internal_state_updates_properties_and_increments_chapter(): void
    {
        $saga = Saga::spawn('saga_1', 'universe_1', $this->heroState, $this->mythVector);

        // Dummy updated state
        $profile = HeroProfile::create(\WorldOS\Simulation\Domain\Engine\ValueObject\StateVector::DIMENSION_ENTROPY, 0.5);
        $stateArr = HeroStateVector::genesis($profile)->toArray();
        $stateArr[HeroStateVector::DIM_STRESS] = 0.5;
        $updatedHero = HeroState::restore(HeroStateVector::fromArray($stateArr));

        $updatedMyth = MythVector::create(['ascension' => 0.8]);
        $updatedMetrics = SagaMetrics::create(0.9, 0.8);

        $saga->evolveInternalState(
            $updatedHero,
            $updatedMyth,
            LifecyclePhase::EMERGENCE,
            $updatedMetrics,
            true // increment chapter
        );

        $this->assertEquals($updatedHero, $saga->getHeroState());
        $this->assertEquals($updatedMyth, $saga->getMythVector());
        $this->assertSame(LifecyclePhase::EMERGENCE, $saga->getLifecyclePhase());
        $this->assertSame(1, $saga->getChapterCount());
    }

    public function test_append_arc_adds_to_history(): void
    {
        $saga = Saga::spawn('saga_1', 'universe_1', $this->heroState, $this->mythVector);

        $saga->appendArc('arc_1');
        $saga->appendArc('arc_2');

        $this->assertSame(['arc_1', 'arc_2'], $saga->getArcHistory());
    }

    public function test_conclude_with_attractor_sets_type(): void
    {
        $saga = Saga::spawn('saga_1', 'universe_1', $this->heroState, $this->mythVector);

        $saga->concludeWithAttractor('REWRITE');

        $this->assertSame('REWRITE', $saga->getAttractorType());
    }

    public function test_cannot_change_attractor_if_archived(): void
    {
        $saga = Saga::spawn('saga_1', 'universe_1', $this->heroState, $this->mythVector);
        
        $saga->archive();

        $this->expectException(InvalidArgumentException::class);
        $saga->concludeWithAttractor('COLLAPSE');
    }
}
