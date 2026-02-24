<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Heroes;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Heroes\UpdateHero\UpdateHeroCommand;
use WorldOS\Legacy\Application\Heroes\UpdateHero\UpdateHeroHandler;
use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Exception\HeroNotFoundException;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;

final class UpdateHeroHandlerTest extends TestCase
{
    public function test_handle_updates_name_and_saves(): void
    {
        $profile = HeroProfile::create('transcendence', 0.5);
        $hero = Hero::create('Old Name', 'world-1', $profile, null, 'hero-1');
        $saved = [];
        $repo = new class($hero, $saved) implements HeroRepositoryInterface {
            public function __construct(private Hero $hero, private array &$saved) {}
            public function findById(string $id): ?Hero { 
                return $id === $this->hero->getId() ? $this->hero : null; 
            }
            public function save(Hero $hero): void { $this->saved[] = $hero; }
            public function delete(string $id): void {}
            public function findByWorld(string $worldId): array { return []; }
            public function findAll(): array { return []; }
        };
        $handler = new UpdateHeroHandler($repo);
        $handler->handle(new UpdateHeroCommand('hero-1', 'New Hero Name'));
        $this->assertCount(1, $saved);
        $this->assertSame('hero-1', $saved[0]->getId());
        $this->assertSame('New Hero Name', $saved[0]->getName());
        $this->assertSame('world-1', $saved[0]->getWorldId());
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
        $handler = new UpdateHeroHandler($repo);
        $this->expectException(HeroNotFoundException::class);
        $handler->handle(new UpdateHeroCommand('h-missing', 'Any'));
    }
}
