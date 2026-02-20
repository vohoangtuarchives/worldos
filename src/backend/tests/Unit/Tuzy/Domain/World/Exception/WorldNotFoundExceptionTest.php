<?php

namespace Tests\Unit\Tuzy\Domain\World\Exception;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\World\Exception\WorldNotFoundException;

final class WorldNotFoundExceptionTest extends TestCase
{
    public function test_with_id_returns_exception_with_message(): void
    {
        $e = WorldNotFoundException::withId('missing-id');
        $this->assertInstanceOf(WorldNotFoundException::class, $e);
        $this->assertStringContainsString('missing-id', $e->getMessage());
    }
}
