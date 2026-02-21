<?php

namespace Tests\Unit\Domains\Saga;

use Tuzy\Application\Saga\Services\ParetoFrontManager;
use Tuzy\Application\Saga\Services\StabilityConstraint;
use PHPUnit\Framework\TestCase;

class ParetoFrontManagerTest extends TestCase
{
    public function test_dominates_when_a_better_on_one_and_not_worse_on_others(): void
    {
        $manager = new ParetoFrontManager(new StabilityConstraint());
        $a = ['stability' => 0.8, 'resilience' => 0.7];
        $b = ['stability' => 0.7, 'resilience' => 0.7];
        $this->assertTrue($manager->dominates($a, $b));
    }

    public function test_does_not_dominate_when_b_better_on_one(): void
    {
        $manager = new ParetoFrontManager(new StabilityConstraint());
        $a = ['stability' => 0.8, 'resilience' => 0.6];
        $b = ['stability' => 0.7, 'resilience' => 0.9];
        $this->assertFalse($manager->dominates($a, $b));
    }

    public function test_does_not_dominate_when_equal(): void
    {
        $manager = new ParetoFrontManager(new StabilityConstraint());
        $a = ['stability' => 0.7, 'resilience' => 0.7];
        $b = ['stability' => 0.7, 'resilience' => 0.7];
        $this->assertFalse($manager->dominates($a, $b));
    }
}
