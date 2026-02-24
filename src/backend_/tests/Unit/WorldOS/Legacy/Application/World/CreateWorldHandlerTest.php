<?php

namespace Tests\Unit\WorldOS\Legacy\Application\World;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\World\CreateWorld\CreateWorldCommand;
use WorldOS\Legacy\Application\World\CreateWorld\CreateWorldHandler;
use WorldOS\Legacy\Application\World\CreateWorld\CreateWorldResult;
use WorldOS\Blueprint\Domain\Legacy\Entity\World;
use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

final class CreateWorldHandlerTest extends TestCase
{
    public function test_handle_creates_world_saves_via_repository_returns_result_with_id(): void
    {
        $saved = [];
        $repo = new class($saved) implements WorldRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?World { return null; }
            public function save(World $world): void { $this->saved[] = $world; }
        };

        $handler = new CreateWorldHandler($repo);
        $command = new CreateWorldCommand('My World');
        $result = $handler->handle($command);

        $this->assertInstanceOf(CreateWorldResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('My World', $result->name);
        $this->assertCount(1, $saved);
        $this->assertSame($result->id, $saved[0]->getId());
        $this->assertSame('My World', $saved[0]->getName());
    }
}
