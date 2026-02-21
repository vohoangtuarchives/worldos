<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\ValueObject\Intent;

final class IntentTest extends TestCase
{
    public function test_from_array_and_to_array(): void
    {
        $i = Intent::fromArray(['type' => 'REVEAL', 'payload' => ['fact' => 'x'], 'confidence' => 0.9]);
        $this->assertSame('REVEAL', $i->type);
        $this->assertSame(0.9, $i->confidence);
        $arr = $i->toArray();
        $this->assertSame('REVEAL', $arr['type']);
    }
}
