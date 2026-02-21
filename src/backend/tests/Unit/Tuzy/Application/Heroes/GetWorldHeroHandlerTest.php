<?php

namespace Tests\Unit\Tuzy\Application\Heroes;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Heroes\GetWorldHero\GetWorldHeroHandler;
use Tuzy\Application\Heroes\GetWorldHero\GetWorldHeroQuery;
use Tuzy\Domain\Heroes\Entity\WorldHero;
use Tuzy\Domain\Heroes\Exception\WorldHeroNotFoundException;
use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class GetWorldHeroHandlerTest extends TestCase
{
    public function test_handle_returns_hero_when_found(): void
    {
        $hero = WorldHero::create('Hero One', 'world-1', 'hero-1');
        $repo = new class($hero) implements WorldHeroRepositoryInterface {
            public function __construct(private ?WorldHero $hero) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?WorldHero { return $this->hero; }
            public function save(WorldHero $h): void {}
        };
        $handler = new GetWorldHeroHandler($repo);
        $result = $handler->handle(new GetWorldHeroQuery('hero-1'));
        $this->assertSame($hero, $result);
        $this->assertSame('hero-1', $result->getId());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements WorldHeroRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?WorldHero { return null; }
            public function save(WorldHero $h): void {}
        };
        $handler = new GetWorldHeroHandler($repo);
        $this->expectException(WorldHeroNotFoundException::class);
        $this->expectExceptionMessage('WorldHero not found: h-missing');
        $handler->handle(new GetWorldHeroQuery('h-missing'));
    }
}
