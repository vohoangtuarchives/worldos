<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service\Extractors;

use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\Evolution\ValueObject\ChronicleEvent;

/**
 * PhaseTransitionExtractor - Detects when the civilization changes its Macro Phase.
 */
final class PhaseTransitionExtractor implements EventExtractor
{
    public function extract(CivilizationSnapshot $prev, CivilizationSnapshot $curr): array
    {
        $events = [];

        if ($prev->historyPhase !== $curr->historyPhase) {
            $events[] = new ChronicleEvent(
                year: $curr->year,
                type: 'PHASE_TRANSITION',
                title: 'Bước ngoặt Kỷ nguyên',
                description: sprintf('Nền văn minh chuyển dịch từ Kỷ nguyên %s sang Kỷ nguyên %s', $prev->historyPhase->name, $curr->historyPhase->name),
                severity: ChronicleEvent::SEVERITY_HIGH,
                metadata: [
                    'from' => $prev->historyPhase->value,
                    'to' => $curr->historyPhase->value
                ]
            );
        }

        return $events;
    }
}
