<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Projection;

use WorldOS\Legacy\Application\Narrative\Planning\ChapterProducer;

/**
 * Renders one narrative chunk (chapter) from context using ChapterProducer.
 */
final class ChapterRenderer
{
    public function __construct(
        private readonly ChapterProducer $producer
    ) {
    }

    public function render(array $context): string
    {
        $blueprint = [
            'chapter_index' => 0,
            'emotional_objective' => $context['focus_event']['type'] ?? 'tension',
            'arc_context' => 'Simulation event: ' . ($context['focus_event']['type'] ?? 'state_change'),
        ];
        return $this->producer->produce($blueprint, $context);
    }
}
