<?php

namespace Tests\Unit\Domains\CognitiveKernel;

use Tests\TestCase;
use App\Domains\CognitiveKernel\Archetype;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

class ArchetypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_archetypes_are_immutable()
    {
        $archetype = Archetype::create([
            'key' => 'hero',
            'domain' => Archetype::DOMAIN_POWER,
            'polarity' => ['order'],
            'baseline_weight' => 0.5,
            'volatility' => 0.1,
            'version' => '1.0.0'
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Constitutional Violation (ADR-1001)');

        $archetype->update(['baseline_weight' => 0.8]);
    }

    public function test_archetypes_cannot_be_deleted()
    {
        $archetype = Archetype::create([
            'key' => 'villain',
            'domain' => Archetype::DOMAIN_POWER,
            'polarity' => ['chaos'],
            'baseline_weight' => 0.5,
            'volatility' => 0.1,
            'version' => '1.0.0'
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Constitutional Violation (ADR-1002)');

        $archetype->delete();
    }

    public function test_timestamps_can_be_updated()
    {
        $archetype = Archetype::create([
            'key' => 'sage',
            'domain' => Archetype::DOMAIN_PERCEPTION,
            'polarity' => ['order'],
            'baseline_weight' => 0.5,
            'volatility' => 0.1,
            'version' => '1.0.0'
        ]);

        // Should not throw exception
        $archetype->touch();
        $this->assertTrue(true);
    }
}
