<?php

namespace Tests\Unit\Tuzy\Domain\World\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\World\ValueObject\GeneVector;

final class GeneVectorTest extends TestCase
{
    public function test_constructor_and_get(): void
    {
        $v = new GeneVector(['preset' => 'martial', 'x' => 1]);
        $this->assertSame('martial', $v->get('preset'));
        $this->assertSame(1, $v->get('x'));
        $this->assertNull($v->get('missing'));
        $this->assertSame(99, $v->get('missing', 99));
    }

    public function test_preset_factories(): void
    {
        $this->assertSame('martial', GeneVector::martial()->get('preset'));
        $this->assertSame('immortal', GeneVector::immortal()->get('preset'));
    }
}
