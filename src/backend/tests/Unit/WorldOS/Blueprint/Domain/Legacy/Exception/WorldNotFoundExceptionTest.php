<?php

namespace Tests\Unit\WorldOS\Blueprint\Domain\Legacy\Exception;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\Legacy\Exception\WorldNotFoundException;

final class WorldNotFoundExceptionTest extends TestCase
{
    public function test_with_id_returns_exception_with_message(): void
    {
        $e = WorldNotFoundException::withId('missing-id');
        $this->assertInstanceOf(WorldNotFoundException::class, $e);
        $this->assertStringContainsString('missing-id', $e->getMessage());
    }
}
