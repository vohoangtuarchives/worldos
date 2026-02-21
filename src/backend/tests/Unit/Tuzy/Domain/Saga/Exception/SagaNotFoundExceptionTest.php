<?php

namespace Tests\Unit\Tuzy\Domain\Saga\Exception;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Saga\Exception\SagaNotFoundException;

final class SagaNotFoundExceptionTest extends TestCase
{
    public function test_with_id_returns_exception_with_message(): void
    {
        $e = SagaNotFoundException::withId('missing-id');
        $this->assertStringContainsString('missing-id', $e->getMessage());
    }
}
