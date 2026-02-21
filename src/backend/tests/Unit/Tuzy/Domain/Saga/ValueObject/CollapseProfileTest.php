<?php

namespace Tests\Unit\Tuzy\Domain\Saga\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Saga\ValueObject\CollapseProfile;

final class CollapseProfileTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $p = new CollapseProfile(0.8, 'entropy_overload', 'entropy_vs_order');
        $this->assertSame(0.8, $p->severity);
        $this->assertSame('entropy_overload', $p->collapseType);
        $this->assertSame('entropy_vs_order', $p->dominantContradiction);
    }

    public function test_from_cause_and_state_infers_type(): void
    {
        $p = CollapseProfile::fromCauseAndState('structural fracture', ['entropy' => 0.3]);
        $this->assertSame('structural_fracture', $p->collapseType);
        $this->assertSame('cohesion_vs_stress', $p->dominantContradiction);
    }
}
