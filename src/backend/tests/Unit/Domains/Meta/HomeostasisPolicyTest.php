<?php

namespace Tests\Unit\Domains\Meta;

use App\Domains\Meta\Policies\HomeostasisPolicy;
use Tests\TestCase;

class HomeostasisPolicyTest extends TestCase
{
    public function test_it_calculates_restoring_force_correctly()
    {
        $policy = new HomeostasisPolicy(0.1); // Gamma = 0.1

        $currentVector = [
            'order' => 0.8, // Excessively high
            'chaos' => 0.2, // Excessively low
            'neutral' => 0.5, // Balanced
        ];

        $forces = $policy->calculateRestoringForce($currentVector, 0);

        // Expected force: (Target - Current) * Gamma
        // Target is 0.5 (default equilibrium)
        // Order force: (0.5 - 0.8) * 0.1 = -0.03
        // Chaos force: (0.5 - 0.2) * 0.1 = +0.03
        // Neutral force: (0.5 - 0.5) * 0.1 = 0

        $this->assertEquals(-0.03, round($forces['order'], 2));
        $this->assertEquals(0.03, round($forces['chaos'], 2));
        $this->assertEquals(0.0, $forces['neutral']);
    }

    public function test_stronger_gamma_yields_stronger_force()
    {
        $policy = new HomeostasisPolicy(0.5); // Strong restoring force

        $currentVector = ['order' => 0.9];
        $forces = $policy->calculateRestoringForce($currentVector, 0);

        // (0.5 - 0.9) * 0.5 = -0.2
        $this->assertEquals(-0.2, $forces['order']);
    }
}
