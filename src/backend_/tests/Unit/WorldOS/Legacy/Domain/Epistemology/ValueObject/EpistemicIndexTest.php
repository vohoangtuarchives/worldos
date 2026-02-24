<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Epistemology\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Epistemology\ValueObject\EpistemicIndex;

final class EpistemicIndexTest extends TestCase
{
    public function test_distortion_probability(): void
    {
        $e = new EpistemicIndex(0.5, 0.5);
        $this->assertEqualsWithDelta(0.25, $e->calculateDistortionProbability(), 1e-9);
    }

    public function test_from_array_and_roundtrip(): void
    {
        $e = EpistemicIndex::fromArray(['instability' => 0.2, 'clarity' => 0.9]);
        $this->assertSame(0.2, $e->instability);
        $this->assertSame(0.9, $e->clarity);
        $arr = $e->toArray();
        $this->assertSame(0.2, $arr['instability']);
    }

    public function test_invalid_bounds_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Indices must be between 0.0 and 1.0');
        new EpistemicIndex(1.5, 0.5);
    }
}
