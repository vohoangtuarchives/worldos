<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Saga;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Saga\ListSagas\ListSagasHandler;
use WorldOS\Legacy\Application\Saga\ListSagas\ListSagasQuery;
use WorldOS\Legacy\Application\Saga\ListSagas\ListSagasResult;
use WorldOS\Saga\Domain\Legacy\Entity\Saga;
use WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface;

final class ListSagasHandlerTest extends TestCase
{
    public function test_handle_returns_all_sagas(): void
    {
        $s1 = Saga::create('S1', 'sid-1');
        $s2 = Saga::create('S2', 'sid-2');
        $repo = new class($s1, $s2) implements SagaRepositoryInterface {
            public function __construct(private Saga $s1, private Saga $s2) {}
            public function findAll(): array { return [$this->s1, $this->s2]; }
            public function findById(string $id): ?Saga { return null; }
            public function save(Saga $s): void {}
        };
        $handler = new ListSagasHandler($repo);
        $result = $handler->handle(new ListSagasQuery());
        $this->assertInstanceOf(ListSagasResult::class, $result);
        $this->assertCount(2, $result->sagas);
        $this->assertSame('sid-1', $result->sagas[0]['id']);
        $this->assertSame('S1', $result->sagas[0]['name']);
    }

    public function test_handle_returns_empty_when_none(): void
    {
        $repo = new class() implements SagaRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?Saga { return null; }
            public function save(Saga $s): void {}
        };
        $handler = new ListSagasHandler($repo);
        $result = $handler->handle(new ListSagasQuery());
        $this->assertSame([], $result->sagas);
    }
}
