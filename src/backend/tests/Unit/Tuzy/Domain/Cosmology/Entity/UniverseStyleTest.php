<?php

namespace Tests\Unit\Tuzy\Domain\Cosmology\Entity;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Cosmology\Entity\UniverseStyle;

final class UniverseStyleTest extends TestCase
{
    public function test_create_returns_entity_with_getters(): void
    {
        $style = UniverseStyle::create('Test Style', 'world-uuid-123');
        $this->assertInstanceOf(UniverseStyle::class, $style);
        $this->assertNotEmpty($style->getId());
        $this->assertSame('Test Style', $style->getName());
        $this->assertSame('world-uuid-123', $style->getWorldId());
    }

    public function test_create_with_explicit_id_uses_that_id(): void
    {
        $style = UniverseStyle::create('Named', 'w1', 'style-id-456');
        $this->assertSame('style-id-456', $style->getId());
        $this->assertSame('w1', $style->getWorldId());
    }
}
