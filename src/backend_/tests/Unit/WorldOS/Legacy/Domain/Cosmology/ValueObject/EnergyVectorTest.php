<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Cosmology\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\EnergyVector;

final class EnergyVectorTest extends TestCase
{
    public function test_from_array_and_to_array(): void
    {
        $v = EnergyVector::fromArray(['manifestation_type' => 'magic', 'accessibility_index' => 0.7]);
        $this->assertSame('magic', $v->manifestationType);
        $this->assertSame(0.7, $v->accessibilityIndex);
        $arr = $v->toArray();
        $this->assertSame('magic', $arr['manifestation_type']);
    }
}
