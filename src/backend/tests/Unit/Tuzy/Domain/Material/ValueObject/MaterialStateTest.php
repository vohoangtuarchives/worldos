<?php

namespace Tests\Unit\Tuzy\Domain\Material\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Material\ValueObject\MaterialState;

final class MaterialStateTest extends TestCase
{
    public function test_cases(): void
    {
        $this->assertSame('stable', MaterialState::STABLE->value);
        $this->assertSame('broken', MaterialState::BROKEN->value);
        $this->assertSame('retired', MaterialState::RETIRED->value);
    }
}
