<?php

namespace Tests\Unit\Tuzy\Domain\Faction\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Faction\ValueObject\IdeologyVector;

final class IdeologyVectorTest extends TestCase
{
    public function test_from_array_and_mutate(): void
    {
        $v = IdeologyVector::fromArray(['militarism' => 0.8, 'purity' => 0.3]);
        $this->assertSame(0.8, $v->militarism);
        $this->assertSame(0.3, $v->purity);

        $mutated = $v->mutate(0.0);
        $this->assertSame($v->militarism, $mutated->militarism);
    }

    public function test_to_array(): void
    {
        $v = new IdeologyVector(0.1, 0.2, 0.3, 0.4, 0.5);
        $arr = $v->toArray();
        $this->assertSame(0.1, $arr['militarism']);
        $this->assertSame(0.5, $arr['purity']);
    }
}
