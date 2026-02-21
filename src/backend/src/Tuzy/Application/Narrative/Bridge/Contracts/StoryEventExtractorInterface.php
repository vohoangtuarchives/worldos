<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Bridge\Contracts;

use Tuzy\Domain\Narrative\ValueObject\StoryEvent;

interface StoryEventExtractorInterface
{
    /**
     * Extract structured story events from chapter content for world mutation.
     *
     * @return list<StoryEvent>
     */
    public function extract(string $chapterContent): array;
}
