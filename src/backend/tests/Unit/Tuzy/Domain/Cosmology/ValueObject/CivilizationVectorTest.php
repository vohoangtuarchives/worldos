<?php

namespace Tests\Unit\Tuzy\Domain\Cosmology\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Cosmology\ValueObject\CivilizationVector;

final class CivilizationVectorTest extends TestCase
{
    public function test_from_array_and_to_array(): void
    {
        $v = CivilizationVector::fromArray(['innovation_rate' => 0.9, 'conflict_drive' => 0.2]);
        $this->assertSame(0.9, $v->innovationRate);
        $this->assertSame(0.2, $v->conflictDrive);
        $arr = $v->toArray();
        $this->assertSame(0.9, $arr['innovation_rate']);
    }
}
