<?php

namespace Tests\Unit\Cosmology;

use App\Domains\Cosmology\Mathematics\Vector;
use PHPUnit\Framework\TestCase;

class VectorTest extends TestCase
{
    public function test_vector_creation_and_access()
    {
        $v = new Vector(['x' => 1.0, 'y' => 2.0]);
        $this->assertEquals(1.0, $v->get('x'));
        $this->assertEquals(2.0, $v->get('y'));
        $this->assertEquals(0.0, $v->get('z')); // Default 0
    }

    public function test_vector_addition()
    {
        $v1 = new Vector(['x' => 1.0, 'y' => 2.0]);
        $v2 = new Vector(['x' => 0.5, 'y' => -1.0, 'z' => 3.0]);

        $v3 = $v1->add($v2);

        $this->assertEquals(1.5, $v3->get('x'));
        $this->assertEquals(1.0, $v3->get('y'));
        $this->assertEquals(3.0, $v3->get('z'));
    }

    public function test_vector_normalization()
    {
        $v = new Vector(['x' => 3.0, 'y' => 4.0]); // Magnitude 5
        $normalized = $v->normalize();

        $this->assertEqualsWithDelta(0.6, $normalized->get('x'), 0.0001); // 3/5
        $this->assertEqualsWithDelta(0.8, $normalized->get('y'), 0.0001); // 4/5
        $this->assertEqualsWithDelta(1.0, $normalized->magnitude(), 0.0001);
    }

    public function test_vector_clamp()
    {
        $v = new Vector(['x' => 1.5, 'y' => -0.5]);
        $clamped = $v->clamp(0.0, 1.0);

        $this->assertEquals(1.0, $clamped->get('x'));
        $this->assertEquals(0.0, $clamped->get('y'));
    }
}
