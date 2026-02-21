<?php

namespace Tests\Unit\Narrative;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Application\Narrative\Services\NarrativeBridge;
use Tuzy\Application\Narrative\Entities\GenreSignature;
use PHPUnit\Framework\TestCase;

class NarrativeBridgePhase7Test extends TestCase
{
    private NarrativeBridge $bridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bridge = new NarrativeBridge();
    }

    public function test_inequality_triggers_revolution_narrative()
    {
        $vector = WorldStateVector::create(
            order: 0.5,
            entropy: 0.5,
            cohesion: 0.5,
            legitimacy: 0.5,
            innovation: 0.5,
            military: 0.5,
            inequality: 0.8, // High Inequality
            trauma: 0.0,
            eliteCohesion: 0.5,
            resourceStock: 0.5
        );

        $context = $this->bridge->generateContext($vector);

        $this->assertStringContainsString("gap between the haves and have-nots", $context);
    }

    public function test_detects_cyberpunk_genre_based_on_inequality()
    {
        // High Innovation + High Inequality = Cyberpunk
        $vector = WorldStateVector::create(
            order: 0.5,
            entropy: 0.5,
            cohesion: 0.2,
            legitimacy: 0.5,
            innovation: 0.9,
            military: 0.5,
            inequality: 0.7,
            trauma: 0.0,
            eliteCohesion: 0.9,
            resourceStock: 0.5
        );

        $genre = $this->bridge->detectGenre($vector);

        $this->assertEquals(GenreSignature::GENRE_CYBERPUNK, $genre->getPrimaryGenre());
    }

    public function test_elite_fracture_narrative()
    {
        // Low Elite Cohesion
        $vector = WorldStateVector::create(
            order: 0.5,
            entropy: 0.5,
            cohesion: 0.5,
            legitimacy: 0.5,
            innovation: 0.5,
            military: 0.5,
            inequality: 0.5,
            trauma: 0.0,
            eliteCohesion: 0.2, // Fractured
            resourceStock: 0.5
        );

        $context = $this->bridge->generateContext($vector);

        $this->assertStringContainsString("civil war among the elites", $context);
    }
}
