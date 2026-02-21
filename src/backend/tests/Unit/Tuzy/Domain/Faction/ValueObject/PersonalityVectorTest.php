<?php

namespace Tests\Unit\Tuzy\Domain\Faction\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Faction\ValueObject\PersonalityVector;

final class PersonalityVectorTest extends TestCase
{
    public function test_from_array_and_inherit(): void
    {
        $p = PersonalityVector::fromArray(['aggression' => 0.8, 'faith' => 0.2]);
        $this->assertSame(0.8, $p->aggression);
        $this->assertSame(0.2, $p->faith);

        $child = $p->inherit($p, 0.0);
        $this->assertSame($p->aggression, $child->aggression);
    }

    public function test_to_array(): void
    {
        $p = new PersonalityVector(0.1, 0.2, 0.3, 0.4, 0.5);
        $arr = $p->toArray();
        $this->assertSame(0.1, $arr['aggression']);
        $this->assertSame(0.5, $arr['ambition']);
    }
}
