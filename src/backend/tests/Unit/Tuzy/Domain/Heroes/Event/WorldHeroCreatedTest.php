<?php

namespace Tests\Unit\Tuzy\Domain\Heroes\Event;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Heroes\Event\WorldHeroCreated;

final class WorldHeroCreatedTest extends TestCase
{
    public function test_event_holds_hero_id_name_world_id(): void
    {
        $event = new WorldHeroCreated('hero-1', 'Hero', 'world-1');
        $this->assertSame('hero-1', $event->heroId);
        $this->assertSame('Hero', $event->name);
        $this->assertSame('world-1', $event->worldId);
    }
}
