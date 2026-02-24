<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Saga\Service;

use WorldOS\Saga\Domain\Saga\ValueObject\LifecyclePhase;
use WorldOS\Saga\Domain\Saga\ValueObject\SagaMetrics;

/**
 * LifecycleManager — Triggers macro transitions across the lifespan of a Saga.
 */
final class LifecycleManager
{
    /**
     * @param int $chapterCount Total chapters written so far
     * @param SagaMetrics $metrics Current performance parameters (NPI, Diversity)
     * @param LifecyclePhase $currentPhase The current phase of the story
     * @param bool $hasReachedAttractor Whether the Ending Attractor Engine has activated
     * 
     * @return LifecyclePhase The new (or same) phase
     */
    public function evaluateNextPhase(
        int            $chapterCount,
        SagaMetrics    $metrics,
        LifecyclePhase $currentPhase,
        bool           $hasReachedAttractor
    ): LifecyclePhase {
        
        // If the story has naturally reached its mythic end, move to Resolution.
        // It bypasses chapter counts because narrative > rigid structure.
        if ($hasReachedAttractor && $currentPhase !== LifecyclePhase::ARCHIVED) {
            return LifecyclePhase::RESOLUTION;
        }

        switch ($currentPhase) {
            case LifecyclePhase::SEED:
                if ($chapterCount > 0) {
                    return LifecyclePhase::EMERGENCE;
                }
                break;

            case LifecyclePhase::EMERGENCE:
                if ($chapterCount >= 40) {
                    return LifecyclePhase::ASCENT;
                }
                break;

            case LifecyclePhase::ASCENT:
                if ($chapterCount >= 150) {
                    return LifecyclePhase::PEAK;
                }
                break;

            case LifecyclePhase::PEAK:
                // Expected Peak behavior: Chapter 150-220
                if ($chapterCount >= 220) {
                    return LifecyclePhase::DESTABILIZE;
                }

                // But if tension (NPI) drops drastically during the peak, early destabilization occurs
                if ($chapterCount >= 180 && $metrics->getNarrativePressureIndex() < 0.2) {
                    return LifecyclePhase::DESTABILIZE;
                }
                break;

            case LifecyclePhase::DESTABILIZE:
                if ($chapterCount >= 260) {
                    return LifecyclePhase::RESOLUTION;
                }
                break;

            case LifecyclePhase::RESOLUTION:
                // Normally an external "ArchiveService" moves this to ARCHIVED once finalizing output
                // But if chapter count overruns wildly, force it to archive.
                if ($chapterCount >= 320) {
                    return LifecyclePhase::ARCHIVED;
                }
                break;

            case LifecyclePhase::ARCHIVED:
                // Terminal state
                return LifecyclePhase::ARCHIVED;
        }

        return $currentPhase;
    }
}
