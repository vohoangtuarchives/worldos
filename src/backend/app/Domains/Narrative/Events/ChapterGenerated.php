<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a serial chapter has been committed (transaction committed).
 * Payload for Kafka/Evolution consumers later.
 */
class ChapterGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $seriesId,
        public int $chapterId,
        public int $bookIndex,
        public int $chapterIndex,
    ) {
    }
}
