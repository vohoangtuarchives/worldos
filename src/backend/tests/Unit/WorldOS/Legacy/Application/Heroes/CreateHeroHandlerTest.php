<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Heroes;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Heroes\CreateHero\CreateHeroCommand;
use WorldOS\Legacy\Application\Heroes\CreateHero\CreateHeroHandler;
use WorldOS\Legacy\Application\Heroes\CreateHero\CreateHeroResult;
use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;

final class CreateHeroHandlerTest extends TestCase
{
    public function test_handle_creates_hero_saves_returns_result(): void
    {
        $saved = [];
        $repo = new class($saved) implements HeroRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findById(string $id): ?Hero { return null; }
            public function save(Hero $hero): void { $this->saved[] = $hero; }
            public function delete(string $id): void {}
            public function findByWorld(string $worldId): array { return []; }
            public function findAll(): array { return []; }
        };
        $handler = new CreateHeroHandler($repo);
        $result = $handler->handle(new CreateHeroCommand('Hero', 'world-1'));
        $this->assertInstanceOf(CreateHeroResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('Hero', $result->name);
        $this->assertSame('world-1', $result->worldId);
        $this->assertCount(1, $saved);
    }
}
