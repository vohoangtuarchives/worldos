<?php

namespace Tests\Unit\WorldOS\Blueprint\Domain\Legacy\Event;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\Legacy\Event\ShockEvent;

final class ShockEventTest extends TestCase
{
    public function test_create_sets_properties(): void
    {
        $e = ShockEvent::create('plague', 0.6, 'north', 0.2, []);
        $this->assertSame('plague', $e->type());
        $this->assertSame(0.6, $e->severity());
        $this->assertSame('north', $e->affectedRegion());
        $this->assertSame(0.2, $e->entropyDelta());
        $this->assertNotEmpty($e->id());
    }

    public function test_plague_factory(): void
    {
        $e = ShockEvent::plague(0.8, 'east');
        $this->assertSame('plague', $e->type());
        $this->assertSame(0.8, $e->severity());
        $this->assertTrue($e->isCatastrophic());
    }

    public function test_is_minor_major_catastrophic(): void
    {
        $this->assertTrue(ShockEvent::create('x', 0.3, 'r', 0)->isMinor());
        $this->assertTrue(ShockEvent::create('x', 0.6, 'r', 0)->isMajor());
        $this->assertTrue(ShockEvent::create('x', 0.9, 'r', 0)->isCatastrophic());
    }
}
