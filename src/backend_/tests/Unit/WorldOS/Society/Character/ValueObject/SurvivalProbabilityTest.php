<?php

namespace Tests\Unit\WorldOS\Society\Character\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Society\Character\ValueObject\SurvivalProbability;

final class SurvivalProbabilityTest extends TestCase
{
    public function test_certain_and_impossible(): void
    {
        $this->assertTrue(SurvivalProbability::certain()->isCertain());
        $this->assertTrue(SurvivalProbability::impossible()->isImpossible());
    }
}
