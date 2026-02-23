<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Legacy\Event;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Legacy\Event\SagaCreated;

final class SagaCreatedTest extends TestCase
{
    public function test_event_holds_saga_id_and_name(): void
    {
        $event = new SagaCreated('saga-1', 'My Saga');
        $this->assertSame('saga-1', $event->sagaId);
        $this->assertSame('My Saga', $event->sagaName);
    }
}
