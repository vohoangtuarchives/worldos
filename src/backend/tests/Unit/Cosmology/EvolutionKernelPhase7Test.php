<?php

namespace Tests\Unit\Cosmology;

use WorldOS\Legacy\Application\Cosmology\Services\BasePhysicsEngine;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use PHPUnit\Framework\TestCase;

class EvolutionKernelPhase7Test extends TestCase
{
    private BasePhysicsEngine $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kernel = new BasePhysicsEngine();
    }

    public function test_inequality_grows_with_high_elite_cohesion_and_low_legitimacy()
    {
        // High Elite Cohesion (Corrupt Oligarchy), Low Legitimacy
        $vector = WorldStateVector::create(
            order: 0.8,
            entropy: 0.1,
            cohesion: 0.5,
            legitimacy: 0.1, // Low
            innovation: 0.5,
            military: 0.5,
            inequality: 0.5,
            trauma: 0.0,
            eliteCohesion: 0.9, // High
            resourceStock: 0.5
        );

        $next = $this->kernel->evolve($vector);

        // Inequality should increase
        $this->assertGreaterThan(0.5, $next->getInequality());
    }

    public function test_trauma_accumulates_during_war_and_chaos()
    {
        // High Military (War), High Entropy (Chaos)
        $vector = WorldStateVector::create(
            order: 0.2,
            entropy: 0.9, // Chaos
            cohesion: 0.2,
            legitimacy: 0.1,
            innovation: 0.1,
            military: 0.9, // War
            inequality: 0.5,
            trauma: 0.1,
            eliteCohesion: 0.2,
            resourceStock: 0.5
        );

        $next = $this->kernel->evolve($vector);

        // Trauma should increase significantly
        $this->assertGreaterThan(0.1, $next->getTrauma());
        
        // Order should collapse due to tipping point (Entropy > 0.85)
        $this->assertLessThan(0.2, $next->getOrder());
    }

    public function test_resource_scarcity_causes_military_collapse()
    {
        // Resources depleted
        $vector = WorldStateVector::create(
            order: 0.5,
            entropy: 0.5,
            cohesion: 0.5,
            legitimacy: 0.5,
            innovation: 0.5,
            military: 0.8,
            inequality: 0.5,
            trauma: 0.0,
            eliteCohesion: 0.5,
            resourceStock: 0.05 // Critical Low
        );

        $next = $this->kernel->evolve($vector);

        // Military should collapse (multiplied by 0.9)
        // Original logic gives small positive dMilitary, but Feedback Loop forces decay
        $this->assertLessThan(0.8, $next->getMilitary());
        
        // Innovation should collapse (multiplied by 0.5)
        $this->assertLessThan(0.5, $next->getInnovation());
    }
}
