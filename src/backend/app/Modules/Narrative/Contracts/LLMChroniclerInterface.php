<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Contracts;

use App\Modules\Shared\ValueObjects\WorldStateVector;
use App\Modules\Universe\ValueObjects\CascadeStateVector;
use App\Modules\WorldTemplate\ValueObjects\GenreKey;

/**
 * LLM Chronicler Contract — converts simulation state into prose.
 *
 * From docs §11.1: State → prose via genre prompt.
 * Input: Entropy/Genre/Events + instruction style.
 * Output: raw chapter text.
 */
interface LLMChroniclerInterface
{
    /**
     * Generate narrative text from simulation state.
     *
     * @param WorldStateVector  $state     Current universe state
     * @param GenreKey          $genre     Genre for narrative style
     * @param array<string>     $events    Recent world events
     * @param array<string,mixed> $context Additional context (StoryBible, etc.)
     * @return string Raw chapter text
     */
    public function chronicle(
        WorldStateVector $state,
        GenreKey $genre,
        ?CascadeStateVector $cascadeState = null,
        array $events = [],
        array $context = [],
    ): string;
}
