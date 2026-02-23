<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Narrative;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Narrative\ValueObject\PressureSignal;

final class PressureSignalTest extends TestCase
{
    public function test_create_clamps_intensity(): void
    {
        $p = PressureSignal::create('u-1', 1.5, 'narrative');
        $this->assertSame(1.0, $p->intensity);
    }

    public function test_constructor_sets_properties(): void
    {
        $p = new PressureSignal('u-1', 0.5, 'narrative', 10, 3);
        $this->assertSame('u-1', $p->universeId);
        $this->assertSame(10, $p->seriesId);
        $this->assertSame(3, $p->chapterSequence);
    }
}
