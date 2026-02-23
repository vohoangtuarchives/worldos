<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Runtime\Exception;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Runtime\Exception\UniverseNotFoundException;

final class UniverseNotFoundExceptionTest extends TestCase
{
    public function test_with_id_returns_exception_with_message(): void
    {
        $e = UniverseNotFoundException::withId('missing-uuid');
        $this->assertInstanceOf(UniverseNotFoundException::class, $e);
        $this->assertStringContainsString('missing-uuid', $e->getMessage());
    }
}
