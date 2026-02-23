<?php

declare(strict_types=1);

namespace App\Listeners;

use WorldOS\Saga\Domain\Narrative\Event\ChapterGenerated;
use Illuminate\Support\Facades\Log;

/**
 * Stub listener for ChapterGenerated. Log or enqueue for Kafka/Evolution later.
 */
class ChapterGeneratedListener
{
    public function handle(ChapterGenerated $event): void
    {
        Log::debug('ChapterGenerated', [
            'series_id' => $event->seriesId,
            'chapter_id' => $event->chapterId,
            'book_index' => $event->bookIndex,
            'chapter_index' => $event->chapterIndex,
        ]);
    }
}
