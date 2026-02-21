<?php

namespace Tests\Unit\Tuzy\Application\World;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\World\UpdateWorld\UpdateWorldCommand;
use Tuzy\Application\World\UpdateWorld\UpdateWorldHandler;
use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class UpdateWorldHandlerTest extends TestCase
{
    public function test_handle_updates_name_and_saves(): void
    {
        $saved = [];
        $repo = new class($saved) implements WorldRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?World {
                return World::create('Old Name', $id);
            }
            public function save(World $world): void {
                $this->saved[] = $world;
            }
        };

        $handler = new UpdateWorldHandler($repo);
        $handler->handle(new UpdateWorldCommand('id-1', 'New Name'));

        $this->assertCount(1, $saved);
        $this->assertSame('id-1', $saved[0]->getId());
        $this->assertSame('New Name', $saved[0]->getName());
    }

    public function test_handle_throws_when_world_not_found(): void
    {
        $repo = new class() implements WorldRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?World { return null; }
            public function save(World $world): void {}
        };

        $handler = new UpdateWorldHandler($repo);

        $this->expectException(WorldNotFoundException::class);
        $this->expectExceptionMessage('World not found: id-missing');
        $handler->handle(new UpdateWorldCommand('id-missing', 'Any Name'));
    }
}
