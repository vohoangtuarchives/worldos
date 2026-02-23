<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Heroes;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Heroes\ListHeroes\ListHeroesHandler;
use WorldOS\Legacy\Application\Heroes\ListHeroes\ListHeroesQuery;
use WorldOS\Legacy\Application\Heroes\ListHeroes\ListHeroesResult;
use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;

final class ListHeroesHandlerTest extends TestCase
{
    public function test_handle_returns_all_heroes_as_result(): void
    {
        $profile = HeroProfile::create('transcendence', 0.5);
        $h1 = Hero::create('Hero One', 'world-1', $profile, null, 'hero-1');
        $h2 = Hero::create('Hero Two', 'world-2', $profile, null, 'hero-2');
        $repo = new class($h1, $h2) implements HeroRepositoryInterface {
            private array $heroes;
            public function __construct(Hero ...$heroes) { $this->heroes = $heroes; }
            public function findById(string $id): ?Hero { return null; }
            public function save(Hero $hero): void {}
            public function delete(string $id): void {}
            public function findByWorld(string $worldId): array { return []; }
            public function findAll(): array { return $this->heroes; }
        };
        $handler = new ListHeroesHandler($repo);
        $result = $handler->handle(new ListHeroesQuery());
        $this->assertInstanceOf(ListHeroesResult::class, $result);
        $this->assertCount(2, $result->Heroes);
        $this->assertSame('hero-1', $result->Heroes[0]['id']);
        $this->assertSame('Hero One', $result->Heroes[0]['name']);
        $this->assertSame('world-1', $result->Heroes[0]['world_id']);
        $this->assertSame('hero-2', $result->Heroes[1]['id']);
        $this->assertSame('Hero Two', $result->Heroes[1]['name']);
        $this->assertSame('world-2', $result->Heroes[1]['world_id']);
    }

    public function test_handle_returns_empty_when_no_heroes(): void
    {
        $repo = new class() implements HeroRepositoryInterface {
            public function findById(string $id): ?Hero { return null; }
            public function save(Hero $hero): void {}
            public function delete(string $id): void {}
            public function findByWorld(string $worldId): array { return []; }
            public function findAll(): array { return []; }
        };
        $handler = new ListHeroesHandler($repo);
        $result = $handler->handle(new ListHeroesQuery());
        $this->assertSame([], $result->Heroes);
    }
}
