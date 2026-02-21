<?php

namespace Tests\Unit\Tuzy\Domain\Character\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Character\ValueObject\SurvivalResult;

final class SurvivalResultTest extends TestCase
{
    public function test_survived_factory_and_to_array(): void
    {
        $r = SurvivalResult::survived('char-1', 0.8, 'Protected');
        $this->assertSame('char-1', $r->characterId);
        $this->assertTrue($r->survived);
        $this->assertSame(0.8, $r->probability);
        $arr = $r->toArray();
        $this->assertSame('char-1', $arr['character_id']);
        $this->assertTrue($arr['survived']);
    }

    public function test_died_factory(): void
    {
        $r = SurvivalResult::died('char-2', 0.2, 'World conditions');
        $this->assertFalse($r->survived);
        $this->assertSame(0.2, $r->probability);
    }
}
