<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Narrative;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Narrative\Event\ChapterGenerated;

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
