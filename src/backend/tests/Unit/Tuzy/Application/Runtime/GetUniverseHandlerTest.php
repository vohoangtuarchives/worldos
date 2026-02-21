<?php

namespace Tests\Unit\Tuzy\Application\Runtime;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Runtime\GetUniverse\GetUniverseHandler;
use Tuzy\Application\Runtime\GetUniverse\GetUniverseQuery;
use Tuzy\Domain\Runtime\Entity\Universe;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;
use Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class GetUniverseHandlerTest extends TestCase
{
    public function test_handle_returns_universe_when_found(): void
    {
        $universe = Universe::create('Test Universe', 'uid-456');
        $repo = new class($universe) implements UniverseRepositoryInterface {
            public function __construct(private ?Universe $universe) {}
            public function findAll(): array { return $this->universe !== null ? [$this->universe] : []; }
            public function findById(string $id): ?Universe { return $this->universe; }
            public function save(Universe $universe): void {}
        };

        $handler = new GetUniverseHandler($repo);
        $result = $handler->handle(new GetUniverseQuery('uid-456'));

        $this->assertSame($universe, $result);
        $this->assertSame('uid-456', $result->getId());
        $this->assertSame('Test Universe', $result->getName());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements UniverseRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?Universe { return null; }
            public function save(Universe $universe): void {}
        };

        $handler = new GetUniverseHandler($repo);

        $this->expectException(UniverseNotFoundException::class);
        $this->expectExceptionMessage('Universe not found: uid-missing');
        $handler->handle(new GetUniverseQuery('uid-missing'));
    }
}
