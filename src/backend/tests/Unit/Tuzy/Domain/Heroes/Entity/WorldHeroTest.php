<?php

namespace Tests\Unit\Tuzy\Domain\Heroes\Entity;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Heroes\Entity\WorldHero;

final class WorldHeroTest extends TestCase
{
    public function test_create_returns_entity_with_getters(): void
    {
        $hero = WorldHero::create('Hero Name', 'world-uuid');
        $this->assertInstanceOf(WorldHero::class, $hero);
        $this->assertNotEmpty($hero->getId());
        $this->assertSame('Hero Name', $hero->getName());
        $this->assertSame('world-uuid', $hero->getWorldId());
    }
}
