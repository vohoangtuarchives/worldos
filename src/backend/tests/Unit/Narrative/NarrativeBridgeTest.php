<?php

namespace Tests\Unit\Narrative;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Narrative\Entities\GenreSignature;
use App\Domains\Narrative\Services\NarrativeBridge;
use PHPUnit\Framework\TestCase;

class NarrativeBridgeTest extends TestCase
{
    private NarrativeBridge $bridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bridge = new NarrativeBridge();
    }

    public function test_detects_utopian_genre()
    {
        // High Order, Cohesion, Low Entropy
        $vector = WorldStateVector::create(0.8, 0.1, 0.9, 0.8, 0.5, 0.1);
        $genre = $this->bridge->detectGenre($vector);

        $this->assertEquals(GenreSignature::GENRE_UTOPIAN, $genre->getPrimaryGenre());
    }

    public function test_detects_cyberpunk_genre()
    {
        // High Innovation, Low Cohesion
        $vector = WorldStateVector::create(0.5, 0.6, 0.2, 0.4, 0.9, 0.5);
        $genre = $this->bridge->detectGenre($vector);

        $this->assertEquals(GenreSignature::GENRE_CYBERPUNK, $genre->getPrimaryGenre());
    }

    public function test_detects_eldritch_horror()
    {
        // Extreme Entropy
        $vector = WorldStateVector::create(0.1, 0.95, 0.1, 0.1, 0.1, 0.1);
        $genre = $this->bridge->detectGenre($vector);

        $this->assertEquals(GenreSignature::GENRE_ELDRITCH, $genre->getPrimaryGenre());
    }

    public function test_context_generation()
    {
        $vector = WorldStateVector::create(0.1, 0.95, 0.1, 0.1, 0.1, 0.1);
        $context = $this->bridge->generateContext($vector);

        $this->assertStringContainsString('The universe is in a state of eldritch', $context);
        $this->assertStringContainsString('Chaos reigns supremely', $context);
    }
}
