<?php

namespace Tests\Unit\WorldOS\Blueprint\Domain\Legacy\Event;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\Legacy\Event\WorldCreated;

final class WorldCreatedTest extends TestCase
{
    public function test_event_holds_world_id_and_name(): void
    {
        $event = new WorldCreated('id-123', 'Test World');
        $this->assertSame('id-123', $event->worldId);
        $this->assertSame('Test World', $event->worldName);
    }
}
