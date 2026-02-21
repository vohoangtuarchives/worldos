<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\ValueObject\EmotionState;

final class EmotionStateTest extends TestCase
{
    public function test_amplify_and_decay(): void
    {
        $e = new EmotionState('fear', 0.5, 0.1);
        $amp = $e->amplify(0.3);
        $this->assertSame(0.8, $amp->intensity);
        $dec = $e->decay();
        $this->assertEqualsWithDelta(0.45, $dec->intensity, 1e-9);
    }
}
