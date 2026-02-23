<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Narrative;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Narrative\ValueObject\StorySlice;

final class StorySliceTest extends TestCase
{
    public function test_constructor_and_roundtrip(): void
    {
        $s = new StorySlice(['p1', 'p2'], 10);
        $this->assertSame(['p1', 'p2'], $s->paragraphs);
        $this->assertSame(10, $s->nextCursor);
        $arr = $s->toArray();
        $r = StorySlice::fromArray($arr);
        $this->assertSame($s->paragraphs, $r->paragraphs);
        $this->assertSame($s->nextCursor, $r->nextCursor);
    }
}
