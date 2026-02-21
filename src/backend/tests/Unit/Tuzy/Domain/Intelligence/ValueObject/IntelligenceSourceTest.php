<?php

namespace Tests\Unit\Tuzy\Domain\Intelligence\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Intelligence\ValueObject\IntelligenceSource;

final class IntelligenceSourceTest extends TestCase
{
    public function test_environment_factory(): void
    {
        $s = IntelligenceSource::environment('world-1', 0.9);
        $this->assertSame('environment', $s->type);
        $this->assertTrue($s->isReliable());
    }
}
