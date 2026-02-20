<?php

namespace Tests\Unit\Tuzy\Application\World;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\World\CreateWorld\CreateWorldCommand;
use Tuzy\Application\World\CreateWorld\CreateWorldHandler;
use Tuzy\Application\World\CreateWorld\CreateWorldResult;
use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class CreateWorldHandlerTest extends TestCase
{
    public function test_handle_creates_world_saves_via_repository_returns_result_with_id(): void
    {
        $saved = [];
        $repo = new class($saved) implements WorldRepositoryInterface {
            public function __construct(private array &$saved) {}
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
