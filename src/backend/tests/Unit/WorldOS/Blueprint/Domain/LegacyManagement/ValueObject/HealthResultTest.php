<?php

namespace Tests\Unit\WorldOS\Blueprint\Domain\LegacyManagement\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus;
use WorldOS\Blueprint\Domain\LegacyManagement\ValueObject\HealthResult;

final class HealthResultTest extends TestCase
{
    public function test_stable_factory(): void
    {
        $r = HealthResult::stable();
        $this->assertSame(WorldHealthStatus::STABLE, $r->status);
        $this->assertSame([], $r->violations);
    }

    public function test_with_violations(): void
    {
        $r = new HealthResult(WorldHealthStatus::CRITICAL, [
            ['code' => 'X', 'message' => 'Msg'],
        ]);
        $this->assertSame(WorldHealthStatus::CRITICAL, $r->status);
        $this->assertCount(1, $r->violations);
    }
}
