<?php

namespace Tests\Unit\Tuzy\Domain\World\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\World\Enums\MagicSystemType;
use Tuzy\Domain\World\Enums\PowerCeiling;
use Tuzy\Domain\World\Enums\TechLevel;
use Tuzy\Domain\World\ValueObject\WorldLawProfile;

final class WorldLawProfileTest extends TestCase
{
    public function test_create_sets_properties(): void
    {
        $vo = new WorldLawProfile(
            MagicSystemType::SPIRITUAL_QI,
            PowerCeiling::IMMORTAL,
            true,
            true,
            0.8,
            TechLevel::DYNASTIC,
            1.0
        );
        $this->assertSame(0.8, $vo->getBeliefToRealityRatio());
        $this->assertTrue($vo->isMythEmergenceEnabled());
        $this->assertSame(MagicSystemType::SPIRITUAL_QI, $vo->magicSystem);
    }

    public function test_two_vos_with_same_data_are_equal(): void
    {
        $a = WorldLawProfile::default();
        $b = WorldLawProfile::default();
        $this->assertTrue($a->equals($b));
    }

    public function test_two_vos_with_different_data_are_not_equal(): void
    {
        $a = WorldLawProfile::default();
        $b = new WorldLawProfile(
            MagicSystemType::NONE,
            PowerCeiling::HUMAN,
            false,
            false,
            0.5,
            TechLevel::PRIMITIVE,
            0.1
        );
        $this->assertFalse($a->equals($b));
    }

    public function test_default_and_fromArray_toArray_roundtrip(): void
    {
        $vo = WorldLawProfile::default();
        $arr = $vo->toArray();
        $restored = WorldLawProfile::fromArray($arr);
        $this->assertTrue($vo->equals($restored));
    }
}
