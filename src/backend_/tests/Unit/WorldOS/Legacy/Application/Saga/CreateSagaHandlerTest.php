<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Saga;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Saga\CreateSaga\CreateSagaCommand;
use WorldOS\Legacy\Application\Saga\CreateSaga\CreateSagaHandler;
use WorldOS\Legacy\Application\Saga\CreateSaga\CreateSagaResult;
use WorldOS\Saga\Domain\Legacy\Entity\Saga;
use WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface;

final class CreateSagaHandlerTest extends TestCase
{
    public function test_handle_creates_saga_saves_via_repository_returns_result_with_id(): void
    {
        $saved = [];
        $repo = new class($saved) implements SagaRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
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
