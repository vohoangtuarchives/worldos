<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\Listeners;

use App\WorldOS\Governance\Services\DecisionEngine;
use App\WorldOS\Runtime\Events\UniverseTicked;

/**
 * Evaluate On Tick Listener.
 *
 * Periodically evaluate the Universe health and act on it
 * (forge, archive, or continue).
 */
final class EvaluateOnTickListener
{
    /**
     * How often to run full governance evaluation.
     */
    private const EVALUATION_INTERVAL_TICKS = 100;

    public function __construct(
        private readonly DecisionEngine $decisionEngine,
    ) {
    }

    public function handle(UniverseTicked $event): void
    {
        // Only evaluate every N ticks to save resources
        if ($event->tick % self::EVALUATION_INTERVAL_TICKS !== 0) {
            return;
        }

        // Evaluate and apply actions (like forking or archiving)
        $this->decisionEngine->evaluateAndAct($event->universeId);
    }
}
