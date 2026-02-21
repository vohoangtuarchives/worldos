<?php

namespace Tests\Unit\Tuzy\Domain\World\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\World\ValueObject\PhysicsProfile;

final class PhysicsProfileTest extends TestCase
{
    public function test_standard_returns_default_values(): void
    {
        $p = PhysicsProfile::standard();
        $this->assertSame(1.0, $p->instability_rate);
        $this->assertSame(0.05, $p->decay_rate);
        $this->assertSame(1000.0, $p->entropy_cap);
    }

    public function test_drift_interpolates(): void
    {
        $a = PhysicsProfile::standard();
        $b = PhysicsProfile::void();
        $c = $a->drift($b, 0.5);
        // standard has higher instability_rate (1.0) than void (0.1), so drift toward void decreases it
        $this->assertLessThan($a->instability_rate, $c->instability_rate);
        $this->assertGreaterThan($b->instability_rate, $c->instability_rate);
    }

    public function test_calculate_drift_returns_normalized_distance(): void
    {
        $a = PhysicsProfile::standard();
        $b = PhysicsProfile::void();
        $d = $a->calculateDrift($b);
        $this->assertGreaterThanOrEqual(0.0, $d);
        $this->assertLessThanOrEqual(1.0, $d);
    }
}
