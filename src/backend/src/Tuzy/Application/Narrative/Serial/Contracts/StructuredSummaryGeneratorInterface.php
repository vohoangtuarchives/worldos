<?php

declare(strict_types=1);

namespace Tuzy\Application\Narrative\Serial\Contracts;

/**
 * Generate structured summary (main_events, character_changes, world_state_delta) from chapter content.
 * Can be implemented with LLM later; stub returns null or minimal structure.
 *
 * @return array{main_events?: list<string>, character_changes?: list<array>, world_state_delta?: array}|null
 */
interface StructuredSummaryGeneratorInterface
{
    public function generate(string $content): ?array;
}
