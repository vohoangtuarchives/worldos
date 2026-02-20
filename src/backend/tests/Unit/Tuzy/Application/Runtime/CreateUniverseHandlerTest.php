<?php

namespace Tests\Unit\Tuzy\Application\Runtime;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Runtime\CreateUniverse\CreateUniverseCommand;
use Tuzy\Application\Runtime\CreateUniverse\CreateUniverseHandler;
use Tuzy\Application\Runtime\CreateUniverse\CreateUniverseResult;
use Tuzy\Domain\Runtime\Entity\Universe;
use Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class CreateUniverseHandlerTest extends TestCase
{
    public function test_handle_creates_universe_saves_via_repository_returns_result_with_id(): void
    {
        $saved = [];
        $repo = new class($saved) implements UniverseRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findById(string $id): ?Universe { return null; }
            public function save(Universe $universe): void { $this->saved[] = $universe; }
        };

        $handler = new CreateUniverseHandler($repo);
        $command = new CreateUniverseCommand('My Universe');
        $result = $handler->handle($command);

        $this->assertInstanceOf(CreateUniverseResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('My Universe', $result->name);
        $this->assertCount(1, $saved);
        $this->assertSame($result->id, $saved[0]->getId());
    }
}
