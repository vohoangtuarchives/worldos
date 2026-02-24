<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Heroes;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Heroes\GetHero\GetHeroHandler;
use WorldOS\Legacy\Application\Heroes\GetHero\GetHeroQuery;
use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Exception\HeroNotFoundException;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;

final class GetHeroHandlerTest extends TestCase
{
    public function test_handle_returns_hero_when_found(): void
    {
        $profile = HeroProfile::create('transcendence', 0.5);
        $hero = Hero::create('Hero One', 'world-1', $profile, null, 'hero-1');
        $repo = new class($hero) implements HeroRepositoryInterface {
            public function __construct(private Hero $hero) {}
            public function findById(string $id): ?Hero { 
                return $id === $this->hero->getId() ? $this->hero : null; 
            }
            public function save(Hero $hero): void {}
            public function delete(string $id): void {}
            public function findByWorld(string $worldId): array { return []; }
            public function findAll(): array { return []; }
        };
        $handler = new GetHeroHandler($repo);
        $result = $handler->handle(new GetHeroQuery('hero-1'));
        $this->assertSame($hero, $result);
        $this->assertSame('hero-1', $result->getId());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements HeroRepositoryInterface {
            public function findById(string $id): ?Hero { return null; }
            public function save(Hero $hero): void {}
            public function delete(string $id): void {}
            public function findByWorld(string $worldId): array { return []; }
            public function findAll(): array { return []; }
        };
        $handler = new GetHeroHandler($repo);
        $this->expectException(HeroNotFoundException::class);
        $handler->handle(new GetHeroQuery('h-missing'));
    }
}
