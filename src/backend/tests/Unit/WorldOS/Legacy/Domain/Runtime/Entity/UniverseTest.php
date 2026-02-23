<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Runtime\Entity;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Runtime\Entity\Universe;

final class UniverseTest extends TestCase
{
    public function test_create_returns_entity_with_get_id_and_get_name(): void
    {
        $universe = Universe::create('Test Universe');
        $this->assertInstanceOf(Universe::class, $universe);
        $this->assertNotEmpty($universe->getId());
        $this->assertSame('Test Universe', $universe->getName());
    }

    public function test_create_with_explicit_id_uses_that_id(): void
    {
        $id = 'custom-universe-123';
        $universe = Universe::create('Named', $id);
        $this->assertSame($id, $universe->getId());
        $this->assertSame('Named', $universe->getName());
    }
}
