<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\ValueObject\MemorySnapshot;

final class MemorySnapshotTest extends TestCase
{
    public function test_is_empty(): void
    {
        $m = new MemorySnapshot('beat', 0.5, [], '', '');
        $this->assertTrue($m->isEmpty());
    }
}
