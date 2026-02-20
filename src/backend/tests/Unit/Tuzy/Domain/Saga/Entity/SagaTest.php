<?php

namespace Tests\Unit\Tuzy\Domain\Saga\Entity;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Saga\Entity\Saga;

final class SagaTest extends TestCase
{
    public function test_create_returns_entity_with_get_id_and_get_name(): void
    {
        $saga = Saga::create('Test Saga');
        $this->assertInstanceOf(Saga::class, $saga);
        $this->assertNotEmpty($saga->getId());
        $this->assertSame('Test Saga', $saga->getName());
    }

    public function test_create_with_explicit_id_uses_that_id(): void
    {
        $id = 'custom-saga-123';
        $saga = Saga::create('Named', $id);
        $this->assertSame($id, $saga->getId());
        $this->assertSame('Named', $saga->getName());
    }
}
