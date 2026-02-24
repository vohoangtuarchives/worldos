<?php

namespace Tests\Unit\WorldOS\Blueprint\Domain\Legacy\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\EntropyScore;

final class EntropyScoreTest extends TestCase
{
    public function test_value_returns_constructor_value(): void
    {
        $score = new EntropyScore(0.5);
        $this->assertSame(0.5, $score->value());
    }

    public function test_to_string_returns_string_value(): void
    {
        $score = new EntropyScore(0.75);
        $this->assertSame('0.75', (string) $score);
    }
}
