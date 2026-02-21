<?php

namespace Tuzy\Domain\Power;

use Tuzy\Domain\Power\ValueObject\PowerStage;

class TransitionNarrativeRule
{
    public function getPhase(string $worldId, int $currentEpoch, array $ledgerHistory): string
    {
        foreach ($ledgerHistory as $event) {
            // If a high-magnitude event happened recently (within 5 epochs)
            if ($event->magnitude > 0.8 && abs($currentEpoch - $event->epoch) <= 5) {
                if ($currentEpoch < $event->epoch) {
                    return 'pre'; // Tích tụ trước biến cố lớn
                }
                if ($currentEpoch === $event->epoch) {
                    return 'moment'; // Khoảnh khắc vỡ vạc
                }
                return 'post'; // Dư chấn sau biến cố
            }
        }
        return 'stable'; 
    }

    public function getConstraints(string $phase, PowerStage $from, PowerStage $to): array
    {
        return match($phase) {
            'pre'    => [
                'forbidden' => ['complacency'],
                'favor'     => ['premonition', 'jitter', 'leaking_vocabulary'],
                'tone'      => 'tightening'
            ],
            'moment' => [
                'forbidden' => ['explanation'],
                'favor'     => ['cataclysm', 'irrevocability'],
                'tone'      => 'shattering'
            ],
            'post'   => [
                'forbidden' => ['old_law_reliance'],
                'favor'     => ['chaos', 're-evaluation', 'obsolescence'],
                'tone'      => 'rebuilding'
            ],
            default => []
        };
    }
}
