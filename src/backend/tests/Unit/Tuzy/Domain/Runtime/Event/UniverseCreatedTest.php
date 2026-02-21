<?php

namespace Tests\Unit\Tuzy\Domain\Runtime\Event;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Runtime\Event\UniverseCreated;

final class UniverseCreatedTest extends TestCase
{
    public function test_event_holds_universe_id_and_name(): void
    {
        $event = new UniverseCreated('uid-1', 'My Universe');
        $this->assertSame('uid-1', $event->universeId);
        $this->assertSame('My Universe', $event->universeName);
    }
}
