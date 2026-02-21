<?php

namespace Tests\Unit\Tuzy\Domain\Character\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Character\ValueObject\RiskFactors;
use Tuzy\Domain\World\Event\ShockEvent;

final class RiskFactorsTest extends TestCase
{
    public function test_empty_and_total_risk(): void
    {
        $r = RiskFactors::empty();
        $this->assertSame(0.0, $r->totalRisk());
    }

    public function test_apply_shock_increases_risk(): void
    {
        $r = RiskFactors::empty();
        $event = ShockEvent::create('plague', 0.5, 'north', 0.2);
        $r2 = $r->applyShock($event);
        $this->assertGreaterThanOrEqual(0.0, $r2->totalRisk());
    }
}
