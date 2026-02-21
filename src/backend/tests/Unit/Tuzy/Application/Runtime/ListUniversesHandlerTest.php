<?php

namespace Tests\Unit\Tuzy\Application\Runtime;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Runtime\ListUniverses\ListUniversesHandler;
use Tuzy\Application\Runtime\ListUniverses\ListUniversesQuery;
use Tuzy\Application\Runtime\ListUniverses\ListUniversesResult;
use Tuzy\Domain\Runtime\Entity\Universe;
use Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class ListUniversesHandlerTest extends TestCase
{
    public function test_handle_returns_all_universes(): void
    {
        $u1 = Universe::create('U1', 'uid-1');
        $u2 = Universe::create('U2', 'uid-2');
        $repo = new class($u1, $u2) implements UniverseRepositoryInterface {
            public function __construct(private Universe $u1, private Universe $u2) {}
            public function findAll(): array { return [$this->u1, $this->u2]; }
            public function findById(string $id): ?Universe { return null; }
            public function save(Universe $u): void {}
        };
        $handler = new ListUniversesHandler($repo);
        $result = $handler->handle(new ListUniversesQuery());
        $this->assertInstanceOf(ListUniversesResult::class, $result);
        $this->assertCount(2, $result->universes);
        $this->assertSame('uid-1', $result->universes[0]['id']);
        $this->assertSame('U1', $result->universes[0]['name']);
    }

    public function test_handle_returns_empty_when_none(): void
    {
        $repo = new class() implements UniverseRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?Universe { return null; }
            public function save(Universe $u): void {}
        };
        $handler = new ListUniversesHandler($repo);
        $result = $handler->handle(new ListUniversesQuery());
        $this->assertSame([], $result->universes);
    }
}
