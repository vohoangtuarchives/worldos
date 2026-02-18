<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Projection;

use App\Domains\Cosmology\Entities\WorldStateVector;

/**
 * Projects simulation state + events into narrative text. Does not modify world state.
 * Flow: FocusSelector → ContextBuilder → ChapterRenderer → store.
 */
final class NarrativeModule
{
    public function __construct(
        private readonly FocusSelector $focusSelector,
        private readonly ContextBuilder $contextBuilder,
        private readonly ChapterRenderer $renderer,
        private readonly NarrativeProjectionRepository $repository
    ) {
    }

    /**
     * @param WorldEventDTO[] $mutationsOrEvents
     */
    public function project(
        WorldStateVector $state,
        array $mutationsOrEvents,
        ?string $universeId = null,
        int $tick = 0
    ): ?string {
        $focus = $this->focusSelector->select($mutationsOrEvents);
        $context = $this->contextBuilder->build($state, $focus, array_slice($mutationsOrEvents, -3));
        $text = $this->renderer->render($context);
        $this->repository->store(
            $text,
            $universeId,
            $tick,
            $focus?->type,
            $focus?->eventId,
            null
        );
        return $text;
    }
}
