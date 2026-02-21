<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\ValueObject\BeatSpec;

final class BeatSpecTest extends TestCase
{
    public function test_to_blueprint_fragment(): void
    {
        $b = new BeatSpec('hope', 0.6, 'rising');
        $f = $b->toBlueprintFragment();
        $this->assertSame('hope', $f['emotional_objective']);
        $this->assertSame(0.6, $f['tension']);
    }
}
