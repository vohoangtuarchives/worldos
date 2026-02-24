<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Narrative;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Narrative\ValueObject\StateSnapshot;

final class StateSnapshotTest extends TestCase
{
    public function test_from_array_and_to_array(): void
    {
        $s = StateSnapshot::fromArray(['active_character_ids' => ['c1'], 'world_tick' => 42]);
        $this->assertSame(['c1'], $s->activeCharacterIds);
        $this->assertSame(42, $s->worldTick);
        $arr = $s->toArray();
        $this->assertSame(42, $arr['world_tick']);
    }
}
