<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Narrative;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Narrative\ValueObject\MemorySnapshot;

final class MemorySnapshotTest extends TestCase
{
    public function test_is_empty(): void
    {
        $m = new MemorySnapshot('beat', 0.5, [], '', '');
        $this->assertTrue($m->isEmpty());
    }
}
