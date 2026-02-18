<?php

namespace Tests\Unit\Domains\Saga;

use App\Domains\Saga\Services\ConvergenceController;
use PHPUnit\Framework\TestCase;

class ConvergenceControllerTest extends TestCase
{
    public function test_exploration_factor_decreases_with_sequence(): void
    {
        $controller = new ConvergenceController(0.02, 20.0);
        $e0 = $controller->explorationFactor(0);
        $e5 = $controller->explorationFactor(5);
        $e20 = $controller->explorationFactor(20);
        $this->assertGreaterThan($e5, $e0);
        $this->assertGreaterThan($e20, $e5);
        $this->assertGreaterThanOrEqual(0.02, $controller->explorationFactor(100));
    }

    public function test_pull_toward_centroid_clamps_delta(): void
    {
        $controller = new ConvergenceController(0.02, 20.0);
        $current = ['stability' => 0.3, 'resilience' => 0.4];
        $centroid = ['stability' => 0.9, 'resilience' => 0.9];
        $pulled = $controller->pullTowardCentroid($current, $centroid, 1.0);
        $this->assertGreaterThan(0.3, $pulled['stability']);
        $this->assertLessThanOrEqual(0.3 + 0.15, $pulled['stability']);
    }

    public function test_centroid_for_saga_returns_null_when_no_data(): void
    {
        $controller = new ConvergenceController(0.02, 20.0);
        $this->assertNull($controller->centroidForSaga(99999, 10));
    }
}
