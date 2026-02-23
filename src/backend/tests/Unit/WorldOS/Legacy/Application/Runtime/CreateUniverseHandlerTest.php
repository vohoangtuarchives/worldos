<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Runtime;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Runtime\CreateUniverse\CreateUniverseCommand;
use WorldOS\Legacy\Application\Runtime\CreateUniverse\CreateUniverseHandler;
use WorldOS\Legacy\Application\Runtime\CreateUniverse\CreateUniverseResult;
use WorldOS\Legacy\Domain\Runtime\Entity\Universe;
use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class CreateUniverseHandlerTest extends TestCase
{
    public function test_handle_creates_universe_saves_via_repository_returns_result_with_id(): void
    {
        $saved = [];
        $repo = new class($saved) implements UniverseRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
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
