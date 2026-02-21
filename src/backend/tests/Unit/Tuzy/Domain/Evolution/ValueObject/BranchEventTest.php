<?php

namespace Tests\Unit\Tuzy\Domain\Evolution\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Evolution\ValueObject\BranchEvent;

final class BranchEventTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $e = new BranchEvent('bifurcation', 'stress_threshold', 0.8, ['dim' => 'x']);
        $this->assertSame('bifurcation', $e->type);
        $this->assertSame('stress_threshold', $e->reason);
        $this->assertSame(0.8, $e->chaosIndex);
        $this->assertSame(['dim' => 'x'], $e->metadata);
    }
}
