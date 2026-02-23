<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Projection;

/**
 * Select the most dramatic event from a list (e.g. highest impact).
 */
final class FocusSelector
{
    public function select(array $events): ?WorldEventDTO
    {
        if ($events === []) {
            return null;
        }
        $best = null;
        $bestImpact = -1.0;
        foreach ($events as $ev) {
            if ($ev instanceof WorldEventDTO && $ev->impact > $bestImpact) {
                $bestImpact = $ev->impact;
                $best = $ev;
            }
        }
        return $best;
    }
}
