<?php

namespace Tests\Unit\WorldOS\Blueprint\Domain\Legacy\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus;

final class WorldHealthStatusTest extends TestCase
{
    public function test_cases_and_color(): void
    {
        $this->assertSame('STABLE', WorldHealthStatus::STABLE->value);
        $this->assertSame('success', WorldHealthStatus::STABLE->color());
        $this->assertSame('danger', WorldHealthStatus::CRITICAL->color());
    }
}
