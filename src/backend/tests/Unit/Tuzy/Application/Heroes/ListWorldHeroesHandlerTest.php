<?php

namespace Tests\Unit\Tuzy\Application\Heroes;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Heroes\ListWorldHeroes\ListWorldHeroesHandler;
use Tuzy\Application\Heroes\ListWorldHeroes\ListWorldHeroesQuery;
use Tuzy\Application\Heroes\ListWorldHeroes\ListWorldHeroesResult;
use Tuzy\Domain\Heroes\Entity\WorldHero;
use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class ListWorldHeroesHandlerTest extends TestCase
{
    public function test_handle_returns_all_heroes_as_result(): void
    {
        $h1 = WorldHero::create('Hero One', 'world-1', 'hero-1');
        $h2 = WorldHero::create('Hero Two', 'world-2', 'hero-2');
        $repo = new class($h1, $h2) implements WorldHeroRepositoryInterface {
            public function __construct(private WorldHero $h1, private WorldHero $h2) {}
            public function findAll(): array { return [$this->h1, $this->h2]; }
            public function findById(string $id): ?WorldHero { return null; }
            public function save(WorldHero $h): void {}
        };
        $handler = new ListWorldHeroesHandler($repo);
        $result = $handler->handle(new ListWorldHeroesQuery());
        $this->assertInstanceOf(ListWorldHeroesResult::class, $result);
        $this->assertCount(2, $result->worldHeroes);
        $this->assertSame('hero-1', $result->worldHeroes[0]['id']);
        $this->assertSame('Hero One', $result->worldHeroes[0]['name']);
        $this->assertSame('world-1', $result->worldHeroes[0]['world_id']);
        $this->assertSame('hero-2', $result->worldHeroes[1]['id']);
        $this->assertSame('Hero Two', $result->worldHeroes[1]['name']);
        $this->assertSame('world-2', $result->worldHeroes[1]['world_id']);
    }

    public function test_handle_returns_empty_when_no_heroes(): void
    {
        $repo = new class() implements WorldHeroRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?WorldHero { return null; }
            public function save(WorldHero $h): void {}
        };
        $handler = new ListWorldHeroesHandler($repo);
        $result = $handler->handle(new ListWorldHeroesQuery());
        $this->assertSame([], $result->worldHeroes);
    }
}
