<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Cosmic\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Cosmic\ValueObject\Attractor;

final class AttractorTest extends TestCase
{
    public function test_catalog_and_find(): void
    {
        $catalog = Attractor::catalog();
        $this->assertCount(4, $catalog);
        $this->assertArrayHasKey('EQUILIBRIUM', $catalog);

        $a = Attractor::find('EQUILIBRIUM');
        $this->assertNotNull($a);
        $this->assertSame(0.20, $a->equilibriumEntropy);
        $this->assertContains('HIGH_CHAOS', $a->transitionsTo);
    }

    public function test_to_array(): void
    {
        $a = Attractor::find('VOID_COLLAPSE');
        $this->assertNotNull($a);
        $arr = $a->toArray();
        $this->assertSame('VOID_COLLAPSE', $arr['code']);
        $this->assertSame(1.30, $arr['bifurcation_threshold']);
    }
}
