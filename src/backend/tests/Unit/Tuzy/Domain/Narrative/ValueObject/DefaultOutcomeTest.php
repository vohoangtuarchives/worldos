<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\ValueObject\DefaultOutcome;

final class DefaultOutcomeTest extends TestCase
{
    public function test_constants_and_constructor(): void
    {
        $o = new DefaultOutcome(DefaultOutcome::RESULT_WIN, 0.8, DefaultOutcome::SCOPE_LOCAL);
        $this->assertSame('win', $o->result);
        $this->assertSame(0.8, $o->intensity);
    }
}