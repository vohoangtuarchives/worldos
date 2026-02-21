<?php

namespace Tests\Unit\Tuzy\Domain\Cosmology\Event;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Cosmology\Event\UniverseStyleCreated;

final class UniverseStyleCreatedTest extends TestCase
{
    public function test_event_holds_id_name_world_id(): void
    {
        $event = new UniverseStyleCreated('style-1', 'Style A', 'world-1');
        $this->assertSame('style-1', $event->universeStyleId);
        $this->assertSame('Style A', $event->name);
        $this->assertSame('world-1', $event->worldId);
    }
}
