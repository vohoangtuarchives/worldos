<?php

namespace Tests\Unit\WorldOS\Legacy\Application\World;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\World\GetWorld\GetWorldHandler;
use WorldOS\Legacy\Application\World\GetWorld\GetWorldQuery;
use WorldOS\Blueprint\Domain\Legacy\Entity\World;
use WorldOS\Blueprint\Domain\Legacy\Exception\WorldNotFoundException;
use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

final class GetWorldHandlerTest extends TestCase
{
    public function test_handle_returns_world_when_found(): void
    {
        $world = World::create('Test World', 'id-123');
        $repo = new class($world) implements WorldRepositoryInterface {
            public function __construct(private ?World $world) {}
            public function findAll(): array { return $this->world !== null ? [$this->world] : []; }
            public function findById(string $id): ?World { return $this->world; }
            public function save(World $world): void {}
        };

        $handler = new GetWorldHandler($repo);
        $result = $handler->handle(new GetWorldQuery('id-123'));

        $this->assertSame($world, $result);
        $this->assertSame('id-123', $result->getId());
        $this->assertSame('Test World', $result->getName());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements WorldRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?World { return null; }
            public function save(World $world): void {}
        };

        $handler = new GetWorldHandler($repo);

        $this->expectException(WorldNotFoundException::class);
        $this->expectExceptionMessage('World not found: id-missing');
        $handler->handle(new GetWorldQuery('id-missing'));
    }
}
