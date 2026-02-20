<?php

namespace Tests\Unit\Tuzy\Application\Vietnamese;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Vietnamese\CreateWorldHero\CreateWorldHeroCommand;
use Tuzy\Application\Vietnamese\CreateWorldHero\CreateWorldHeroHandler;
use Tuzy\Application\Vietnamese\CreateWorldHero\CreateWorldHeroResult;
use Tuzy\Domain\Vietnamese\Entity\WorldHero;
use Tuzy\Domain\Vietnamese\Repository\WorldHeroRepositoryInterface;

final class CreateWorldHeroHandlerTest extends TestCase
{
    public function test_handle_creates_hero_saves_returns_result(): void
    {
        $saved = [];
        $repo = new class($saved) implements WorldHeroRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findById(string $id): ?WorldHero { return null; }
            public function save(WorldHero $h): void { $this->saved[] = $h; }
        };
        $handler = new CreateWorldHeroHandler($repo);
        $result = $handler->handle(new CreateWorldHeroCommand('Hero', 'world-1'));
        $this->assertInstanceOf(CreateWorldHeroResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('Hero', $result->name);
        $this->assertSame('world-1', $result->worldId);
        $this->assertCount(1, $saved);
    }
}
