<?php

namespace Tests\Unit\Tuzy\Domain\Character\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Character\ValueObject\SurvivalTrend;

final class SurvivalTrendTest extends TestCase
{
    public function test_is_declining(): void
    {
        $declining = new SurvivalTrend('char-1', [0.8, 0.6, 0.4]);
        $this->assertTrue($declining->isDeclining());

        $stable = new SurvivalTrend('char-2', [0.5, 0.5]);
        $this->assertFalse($stable->isDeclining());

        $single = new SurvivalTrend('char-3', [0.3]);
        $this->assertFalse($single->isDeclining());
    }

    public function test_average_probability(): void
    {
        $t = new SurvivalTrend('c', [0.2, 0.4, 0.6]);
        $this->assertEqualsWithDelta(0.4, $t->averageProbability(), 1e-9);

        $empty = new SurvivalTrend('c', []);
        $this->assertSame(0.0, $empty->averageProbability());
    }

    public function test_risk_of_death(): void
    {
        $t = new SurvivalTrend('c', [0.1, 0.2, 0.5]);
        $risk = $t->riskOfDeath(3);
        $this->assertGreaterThan(0, $risk);
        $this->assertLessThanOrEqual(1.0, $risk);
    }

    public function test_to_array(): void
    {
        $t = new SurvivalTrend('char-id', [0.7, 0.5]);
        $arr = $t->toArray();
        $this->assertSame('char-id', $arr['character_id']);
        $this->assertSame([0.7, 0.5], $arr['probabilities']);
        $this->assertArrayHasKey('is_declining', $arr);
        $this->assertArrayHasKey('average_probability', $arr);
        $this->assertArrayHasKey('risk_of_death', $arr);
    }
}
