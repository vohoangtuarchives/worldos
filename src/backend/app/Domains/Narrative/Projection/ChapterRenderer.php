<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Projection;

use App\Domains\Narrative\Planning\ChapterProducer;

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
