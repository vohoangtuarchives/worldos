<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Legacy\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Legacy\ValueObject\ShockParams;

final class ShockParamsTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $p = new ShockParams(0.5, 'plague');
        $this->assertSame(0.5, $p->magnitude);
        $this->assertSame('plague', $p->type);
    }
}
