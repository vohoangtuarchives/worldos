<?php

namespace Tests\Unit\Tuzy\Application\Heroes;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Heroes\UpdateWorldHero\UpdateWorldHeroCommand;
use Tuzy\Application\Heroes\UpdateWorldHero\UpdateWorldHeroHandler;
use Tuzy\Domain\Heroes\Entity\WorldHero;
use Tuzy\Domain\Heroes\Exception\WorldHeroNotFoundException;
use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class UpdateWorldHeroHandlerTest extends TestCase
{
    public function test_handle_updates_name_and_saves(): void
    {
        $saved = [];
        $repo = new class($saved) implements WorldHeroRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?WorldHero { return WorldHero::create('Old', 'world-1', $id); }
            public function save(WorldHero $h): void { $this->saved[] = $h; }
        };
        $handler = new UpdateWorldHeroHandler($repo);
        $handler->handle(new UpdateWorldHeroCommand('hero-1', 'New Hero Name'));
        $this->assertCount(1, $saved);
        $this->assertSame('hero-1', $saved[0]->getId());
        $this->assertSame('New Hero Name', $saved[0]->getName());
        $this->assertSame('world-1', $saved[0]->getWorldId());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements WorldHeroRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?WorldHero { return null; }
            public function save(WorldHero $h): void {}
        };
        $handler = new UpdateWorldHeroHandler($repo);
        $this->expectException(WorldHeroNotFoundException::class);
        $handler->handle(new UpdateWorldHeroCommand('h-missing', 'Any'));
    }
}
