<?php

namespace Tests\Unit\Tuzy\Domain\World\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\World\ValueObject\Claim;

final class ClaimTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $c = new Claim('RESURRECTION', 10, 'hero-1', 'north');
        $this->assertSame('RESURRECTION', $c->type);
        $this->assertSame(10, $c->magnitude);
        $this->assertSame('hero-1', $c->subject);
        $this->assertSame('north', $c->location);
    }

    public function test_optional_subject_location_null(): void
    {
        $c = new Claim('SPELL_CAST', 3);
        $this->assertSame('SPELL_CAST', $c->type);
        $this->assertSame(3, $c->magnitude);
        $this->assertNull($c->subject);
        $this->assertNull($c->location);
    }
}
