<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Actions;

use App\WorldOS\Cosmology\ValueObjects\EvolutionResult;
use App\WorldOS\Runtime\Dto\TickUniverseDTO;

/**
 * Advance Universe Action — runs N ticks sequentially.
 *
 * Stops early on collapse. Collects all EvolutionResults for analysis.
 */
final class AdvanceUniverseAction
{
    public function __construct(
        private readonly TickUniverseAction $tickAction,
    ) {
    }

    /**
     * @return EvolutionResult[] Results for each completed tick
     */
    public function handle(string $universeId, int $ticks = 1): array
    {
        $results = [];

        for ($i = 0; $i < $ticks; $i++) {
            $dto = new TickUniverseDTO(universeId: $universeId);
            $result = $this->tickAction->handle($dto);
            $results[] = $result;

            // Stop early on collapse
            if ($result->collapseDetected) {
                break;
            }
        }

        return $results;
    }
}
