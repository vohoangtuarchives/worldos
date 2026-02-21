<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\Event;

/**
 * Fired after a serial chapter has been committed (transaction committed).
 * Payload for Kafka/Evolution consumers later.
 */
readonly class ChapterGenerated
{
    public function __construct(
        public string $seriesId,
        public int $chapterId,
        public int $bookIndex,
        public int $chapterIndex,
    ) {
    }
}
