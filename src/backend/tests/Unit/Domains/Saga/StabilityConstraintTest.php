<?php

namespace Tests\Unit\Domains\Saga;

use App\Domains\Saga\Services\StabilityConstraint;
use PHPUnit\Framework\TestCase;

class StabilityConstraintTest extends TestCase
{
    public function test_violated_when_resilience_low(): void
    {
        $constraint = new StabilityConstraint();
        $this->assertTrue($constraint->violated([
            'resilience' => 0.3,
            'entropy_control' => 0.6,
        ]));
    }

    public function test_violated_when_entropy_control_low(): void
    {
        $constraint = new StabilityConstraint();
        $this->assertTrue($constraint->violated([
            'resilience' => 0.6,
            'entropy_control' => 0.3,
        ]));
    }

    public function test_not_violated_when_above_thresholds(): void
    {
        $constraint = new StabilityConstraint();
        $this->assertFalse($constraint->violated([
            'resilience' => 0.5,
            'entropy_control' => 0.5,
        ]));
    }
}
