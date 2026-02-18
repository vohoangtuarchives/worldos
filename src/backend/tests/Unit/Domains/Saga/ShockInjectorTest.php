<?php

namespace Tests\Unit\Domains\Saga;

use App\Domains\Saga\Services\ShockInjector;
use PHPUnit\Framework\TestCase;

class ShockInjectorTest extends TestCase
{
    public function test_should_inject_returns_false_when_disabled(): void
    {
        $injector = new ShockInjector(false, 75, 0.1, 0.3);
        $this->assertFalse($injector->shouldInject(1, 75));
    }

    public function test_should_inject_returns_false_at_year_zero(): void
    {
        $injector = new ShockInjector(true, 75, 0.1, 0.3);
        $this->assertFalse($injector->shouldInject(1, 0));
    }

    public function test_should_inject_returns_true_at_interval(): void
    {
        $injector = new ShockInjector(true, 75, 0.1, 0.3);
        $this->assertTrue($injector->shouldInject(1, 75));
        $this->assertTrue($injector->shouldInject(1, 150));
    }

    public function test_should_inject_returns_false_between_intervals(): void
    {
        $injector = new ShockInjector(true, 75, 0.1, 0.3);
        $this->assertFalse($injector->shouldInject(1, 74));
        $this->assertFalse($injector->shouldInject(1, 76));
    }

    public function test_magnitude_in_range(): void
    {
        $injector = new ShockInjector(true, 75, 0.1, 0.3);
        for ($i = 0; $i < 20; $i++) {
            $m = $injector->magnitude(1, 75);
            $this->assertGreaterThanOrEqual(0.1, $m);
            $this->assertLessThanOrEqual(0.3, $m);
        }
    }

    public function test_shock_type_returns_valid_type(): void
    {
        $injector = new ShockInjector(true, 75, 0.1, 0.3);
        $types = ['military', 'resource', 'ideology', 'tech'];
        for ($year = 0; $year < 10; $year++) {
            $type = $injector->shockType(1, $year);
            $this->assertContains($type, $types);
        }
    }
}
