<?php

namespace Tests\Unit\Tuzy\Application\Saga;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Saga\UpdateSaga\UpdateSagaCommand;
use Tuzy\Application\Saga\UpdateSaga\UpdateSagaHandler;
use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Exception\SagaNotFoundException;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

final class UpdateSagaHandlerTest extends TestCase
{
    public function test_handle_updates_name_and_saves(): void
    {
        $saved = [];
        $repo = new class($saved) implements SagaRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?Saga { return Saga::create('Old', $id); }
            public function save(Saga $saga): void { $this->saved[] = $saga; }
        };
        $handler = new UpdateSagaHandler($repo);
        $handler->handle(new UpdateSagaCommand('sid-1', 'New Saga Name'));
        $this->assertCount(1, $saved);
        $this->assertSame('sid-1', $saved[0]->getId());
        $this->assertSame('New Saga Name', $saved[0]->getName());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements SagaRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?Saga { return null; }
            public function save(Saga $saga): void {}
        };
        $handler = new UpdateSagaHandler($repo);
        $this->expectException(SagaNotFoundException::class);
        $this->expectExceptionMessage('Saga not found: sid-missing');
        $handler->handle(new UpdateSagaCommand('sid-missing', 'Any'));
    }
}
