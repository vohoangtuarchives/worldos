<?php

namespace Tests\Unit\Tuzy\Domain\Saga\Event;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Saga\Event\SagaCreated;

final class SagaCreatedTest extends TestCase
{
    public function test_event_holds_saga_id_and_name(): void
    {
        $event = new SagaCreated('saga-1', 'My Saga');
        $this->assertSame('saga-1', $event->sagaId);
        $this->assertSame('My Saga', $event->sagaName);
    }
}
