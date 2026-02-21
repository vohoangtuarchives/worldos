<?php

namespace Tests\Unit\Tuzy\Domain\Character\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Character\ValueObject\NarrativeWeight;

final class NarrativeWeightTest extends TestCase
{
    public function test_main_has_high_importance(): void
    {
        $w = NarrativeWeight::main();
        $this->assertTrue($w->isMainCharacter());
    }
}
