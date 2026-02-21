<?php

namespace Tests\Unit\Tuzy\Application\World;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\World\ListWorlds\ListWorldsHandler;
use Tuzy\Application\World\ListWorlds\ListWorldsQuery;
use Tuzy\Application\World\ListWorlds\ListWorldsResult;
use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class ListWorldsHandlerTest extends TestCase
{
    public function test_handle_returns_all_worlds_as_result(): void
    {
        $w1 = World::create('First', 'id-1');
        $w2 = World::create('Second', 'id-2');
        $repo = new class($w1, $w2) implements WorldRepositoryInterface {
            public function __construct(private World $w1, private World $w2) {}
            public function findAll(): array { return [$this->w1, $this->w2]; }
            public function findById(string $id): ?World { return null; }
            public function save(World $world): void {}
        };
        $handler = new ListWorldsHandler($repo);
        $result = $handler->handle(new ListWorldsQuery());
        $this->assertInstanceOf(ListWorldsResult::class, $result);
        $this->assertCount(2, $result->worlds);
        $this->assertSame('id-1', $result->worlds[0]['id']);
        $this->assertSame('First', $result->worlds[0]['name']);
        $this->assertSame('id-2', $result->worlds[1]['id']);
        $this->assertSame('Second', $result->worlds[1]['name']);
    }

    public function test_handle_returns_empty_when_no_worlds(): void
    {
        $repo = new class() implements WorldRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?World { return null; }
            public function save(World $world): void {}
        };
        $handler = new ListWorldsHandler($repo);
        $result = $handler->handle(new ListWorldsQuery());
        $this->assertSame([], $result->worlds);
    }
}
