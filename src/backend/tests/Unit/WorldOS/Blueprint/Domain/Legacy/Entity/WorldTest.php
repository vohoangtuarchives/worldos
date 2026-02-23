<?php

namespace Tests\Unit\WorldOS\Blueprint\Domain\Legacy\Entity;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\Legacy\Entity\World;

final class WorldTest extends TestCase
{
    public function test_create_returns_entity_with_get_id_and_get_name(): void
    {
        $world = World::create('Test World');
        $this->assertInstanceOf(World::class, $world);
        $this->assertNotEmpty($world->getId());
        $this->assertSame('Test World', $world->getName());
    }

    public function test_create_with_explicit_id_uses_that_id(): void
    {
        $id = 'custom-uuid-123';
        $world = World::create('Named', $id);
        $this->assertSame($id, $world->getId());
        $this->assertSame('Named', $world->getName());
    }

    public function test_create_without_id_generates_identity(): void
    {
        $world = World::create('Generated');
        $this->assertNotEmpty($world->getId());
        $this->assertNotEquals('', $world->getId());
    }
}
