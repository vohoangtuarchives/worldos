<?php

namespace Tests\Unit\WorldOS\Society\Character\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Society\Character\ValueObject\NarrativeWeight;

final class NarrativeWeightTest extends TestCase
{
    public function test_main_has_high_importance(): void
    {
        $w = NarrativeWeight::main();
        $this->assertTrue($w->isMainCharacter());
    }
}
