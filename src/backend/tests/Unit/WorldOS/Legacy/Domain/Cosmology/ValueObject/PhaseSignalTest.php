<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Cosmology\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\PhaseSignal;

final class PhaseSignalTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $s = new PhaseSignal('CRITICAL', 'zone-1', 0.9, true, false, 0.5);
        $this->assertSame('CRITICAL', $s->phase);
        $this->assertSame(0.9, $s->pressure);
        $this->assertTrue($s->shouldCollapse);
    }

    public function test_from_assessment(): void
    {
        $s = PhaseSignal::fromAssessment(['phase' => 'STABLE', 'pressure' => 0.2]);
        $this->assertSame('STABLE', $s->phase);
        $this->assertSame(0.2, $s->pressure);
    }
}
