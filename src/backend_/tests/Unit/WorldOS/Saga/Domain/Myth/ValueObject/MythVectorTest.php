<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Saga\Domain\Myth\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;

final class MythVectorTest extends TestCase
{
    public function test_opposition_constraints_are_enforced_between_ascension_and_corruption(): void
    {
        $vector = MythVector::create([
            MythVector::DIM_ASCENSION => 0.8,
            MythVector::DIM_CORRUPTION => 0.8,
            MythVector::DIM_RECURSION => 0.1,
            MythVector::DIM_ESCAPE => 0.1,
            MythVector::DIM_CONVERGENCE => 0.1,
        ]);

        // 0.8 + 0.8 = 1.6 > 1.0, so it should be normalized to 0.5 and 0.5
        $this->assertEquals(0.5, $vector->get(MythVector::DIM_ASCENSION));
        $this->assertEquals(0.5, $vector->get(MythVector::DIM_CORRUPTION));
    }

    public function test_opposition_constraints_are_enforced_between_escape_and_convergence(): void
    {
        $vector = MythVector::create([
            MythVector::DIM_ASCENSION => 0.1,
            MythVector::DIM_CORRUPTION => 0.1,
            MythVector::DIM_RECURSION => 0.1,
            MythVector::DIM_ESCAPE => 0.9,
            MythVector::DIM_CONVERGENCE => 0.9,
        ]);

        // 0.9 + 0.9 = 1.8 > 1.0, so it should be normalized to 0.5 and 0.5
        $this->assertEquals(0.5, $vector->get(MythVector::DIM_ESCAPE));
        $this->assertEquals(0.5, $vector->get(MythVector::DIM_CONVERGENCE));
    }
}
