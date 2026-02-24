<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\CoreTruth\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\CoreTruth\ValueObject\Axiom;

final class AxiomTest extends TestCase
{
    public function test_constructor_and_to_array(): void
    {
        $a = new Axiom('ax1', 'Magic is finite', true);
        $this->assertSame('ax1', $a->id);
        $this->assertTrue($a->isAbsolute);
        $arr = $a->toArray();
        $this->assertSame('ax1', $arr['id']);
        $this->assertTrue($arr['is_absolute']);
    }

    public function test_from_array(): void
    {
        $a = Axiom::fromArray(['id' => 'x', 'description' => 'Desc', 'is_absolute' => false]);
        $this->assertSame('x', $a->id);
        $this->assertFalse($a->isAbsolute);
    }

    public function test_empty_id_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Axiom requires valid id and description');
        new Axiom('', 'desc');
    }
}
