<?php

namespace Tests\Unit\Tuzy\Domain\World\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\World\ValueObject\WorldLawProfile;

final class WorldLawProfileTest extends TestCase
{
    public function test_create_sets_properties(): void
    {
        $vo = new WorldLawProfile(0.8, true);
        $this->assertSame(0.8, $vo->getBeliefToRealityRatio());
        $this->assertTrue($vo->isMythEmergenceEnabled());
    }

    public function test_two_vos_with_same_data_are_equal(): void
    {
        $a = new WorldLawProfile(0.8, true);
        $b = new WorldLawProfile(0.8, true);
        $this->assertTrue($a->equals($b));
    }

    public function test_two_vos_with_different_data_are_not_equal(): void
    {
        $a = new WorldLawProfile(0.8, true);
        $b = new WorldLawProfile(0.5, true);
        $this->assertFalse($a->equals($b));
    }
}
