<?php

namespace Tests\Unit\Tuzy\Application\Saga;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Saga\CreateSaga\CreateSagaCommand;
use Tuzy\Application\Saga\CreateSaga\CreateSagaHandler;
use Tuzy\Application\Saga\CreateSaga\CreateSagaResult;
use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

final class CreateSagaHandlerTest extends TestCase
{
    public function test_handle_creates_saga_saves_via_repository_returns_result_with_id(): void
    {
        $saved = [];
        $repo = new class($saved) implements SagaRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findById(string $id): ?Saga { return null; }
            public function save(Saga $saga): void { $this->saved[] = $saga; }
        };

        $handler = new CreateSagaHandler($repo);
        $command = new CreateSagaCommand('My Saga');
        $result = $handler->handle($command);

        $this->assertInstanceOf(CreateSagaResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('My Saga', $result->name);
        $this->assertCount(1, $saved);
        $this->assertSame($result->id, $saved[0]->getId());
    }
}
