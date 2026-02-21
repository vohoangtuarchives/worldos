<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\Event;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\Event\ChapterGenerated;

final class ChapterGeneratedTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $e = new ChapterGenerated('series-1', 5, 1, 4);
        $this->assertSame('series-1', $e->seriesId);
        $this->assertSame(5, $e->chapterId);
        $this->assertSame(1, $e->bookIndex);
        $this->assertSame(4, $e->chapterIndex);
    }
}
