<?php

namespace Tests\Unit\Tuzy\Application\Saga;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Saga\GetSaga\GetSagaHandler;
use Tuzy\Application\Saga\GetSaga\GetSagaQuery;
use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Exception\SagaNotFoundException;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

final class GetSagaHandlerTest extends TestCase
{
    public function test_handle_returns_saga_when_found(): void
    {
        $saga = Saga::create('Test Saga', 'saga-789');
        $repo = new class($saga) implements SagaRepositoryInterface {
            public function __construct(private ?Saga $saga) {}
            public function findAll(): array { return $this->saga !== null ? [$this->saga] : []; }
            public function findById(string $id): ?Saga { return $this->saga; }
            public function save(Saga $saga): void {}
        };

        $handler = new GetSagaHandler($repo);
        $result = $handler->handle(new GetSagaQuery('saga-789'));

        $this->assertSame($saga, $result);
        $this->assertSame('saga-789', $result->getId());
        $this->assertSame('Test Saga', $result->getName());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements SagaRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?Saga { return null; }
            public function save(Saga $saga): void {}
        };

        $handler = new GetSagaHandler($repo);

        $this->expectException(SagaNotFoundException::class);
        $this->expectExceptionMessage('Saga not found: saga-missing');
        $handler->handle(new GetSagaQuery('saga-missing'));
    }
}
