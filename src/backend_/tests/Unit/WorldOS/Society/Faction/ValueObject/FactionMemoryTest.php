<?php

namespace Tests\Unit\WorldOS\Society\Faction\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Society\Faction\ValueObject\FactionMemory;

final class FactionMemoryTest extends TestCase
{
    public function test_fresh_and_roundtrip(): void
    {
        $m = FactionMemory::fresh();
        $this->assertSame(0.0, $m->successScore);
        $this->assertSame([], $m->intentHistory);

        $m2 = new FactionMemory(0.5, 0.1, 0.2, [['a' => 1]]);
        $arr = $m2->toArray();
        $restored = FactionMemory::fromArray($arr);
        $this->assertSame(0.5, $restored->successScore);
        $this->assertSame([['a' => 1]], $restored->intentHistory);
    }
}
