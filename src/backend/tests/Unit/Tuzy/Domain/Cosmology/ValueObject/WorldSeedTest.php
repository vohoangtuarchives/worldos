<?php

namespace Tests\Unit\Tuzy\Domain\Cosmology\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Cosmology\ValueObject\WorldSeed;

final class WorldSeedTest extends TestCase
{
    public function test_from_array_and_to_array(): void
    {
        $data = [
            'archetype_id' => 'test-archetype',
            'ontology' => ['energy_density' => 0.7],
            'epistemic' => [],
            'civilization' => [],
            'energy' => ['manifestation_type' => 'magic'],
        ];
        $s = WorldSeed::fromArray($data);
        $this->assertSame('test-archetype', $s->archetypeId);
        $this->assertSame(0.7, $s->ontology->energyDensity);
        $this->assertSame('magic', $s->energy->manifestationType);
        $arr = $s->toArray();
        $this->assertSame('test-archetype', $arr['archetype_id']);
    }
}
