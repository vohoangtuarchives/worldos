<?php

namespace Tests\Unit\Tuzy\Domain\Cosmology\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Cosmology\ValueObject\EpistemicVector;

final class EpistemicVectorTest extends TestCase
{
    public function test_from_array_and_to_array(): void
    {
        $v = EpistemicVector::fromArray([
            'epistemic_stability' => 0.8,
            'belief_fragmentation' => 0.1,
        ]);
        $this->assertSame(0.8, $v->epistemicStability);
        $this->assertSame(0.1, $v->beliefFragmentation);
        $arr = $v->toArray();
        $this->assertSame(0.8, $arr['epistemic_stability']);
    }
}
