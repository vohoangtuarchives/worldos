<?php

namespace Tests\Unit\Tuzy\Domain\Conflict\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Conflict\ValueObject\ConflictSeed;

final class ConflictSeedTest extends TestCase
{
    public function test_class_struggle_factory(): void
    {
        $s = ConflictSeed::classStruggle(0.7);
        $this->assertSame(ConflictSeed::TYPE_CLASS_STRUGGLE, $s->type);
        $this->assertSame(0.7, $s->intensity);
        $this->assertSame(ConflictSeed::STABILITY_BUILDING, $s->stability);
    }

    public function test_rebellion_potential(): void
    {
        $s = ConflictSeed::rebellionPotential(0.5, ConflictSeed::STABILITY_VOLATILE);
        $this->assertSame(ConflictSeed::TYPE_REBELLION_POTENTIAL, $s->type);
        $this->assertSame(ConflictSeed::STABILITY_VOLATILE, $s->stability);
    }
}
