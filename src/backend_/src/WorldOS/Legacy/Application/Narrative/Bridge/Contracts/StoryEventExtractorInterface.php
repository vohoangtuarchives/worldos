<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Bridge\Contracts;

use WorldOS\Saga\Domain\Narrative\ValueObject\StoryEvent;

interface StoryEventExtractorInterface
{
    /**
     * Extract structured story events from chapter content for world mutation.
     *
     * @return list<StoryEvent>
     */
    public function extract(string $chapterContent): array;
}
