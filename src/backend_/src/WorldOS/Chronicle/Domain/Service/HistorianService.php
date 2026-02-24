<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\Service;

use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\ValueObject\HistoricalEra;

/**
 * HistorianService — The "Writer" of WorldOS history.
 * Analyzes raw events to synthesize them into meaningful eras.
 */
final class HistorianService
{
    /**
     * Synthesize history into a list of eras.
     * 
     * @param ChronicleEvent[] $events    Sorted by tick ascending
     * @param int|null         $upToTick  Optional cap for the final era's endTick
     */
    public function synthesizeEras(array $events, ?int $upToTick = null): array
    {
        if (empty($events)) {
            return [];
        }

        // 1. Ensure events are sorted by tick ascending
        usort($events, fn(ChronicleEvent $a, ChronicleEvent $b) => $a->getTick() <=> $b->getTick());

        $eras = [];
        $currentEvents = [];
        $currentEraStart = $events[0]->getTick();
        
        foreach ($events as $event) {
            // Break only if:
            // 1. It's a breakpoint
            // 2. We've actually entered a NEW tick relative to current era start (avoid same-tick fragmentation)
            // 3. We have events to finalize
            if ($this->isEraBreakpoint($event, $currentEvents) 
                && $event->getTick() > $currentEraStart 
                && !empty($currentEvents)
            ) {
                $endTick = $event->getTick() - 1;
                $eras[] = $this->createEra($currentEraStart, $endTick, $currentEvents);
                $currentEraStart = $event->getTick();
                $currentEvents = [];
            }
            $currentEvents[] = $event;
        }

        // 2. Add final era with the remaining events
        if (!empty($currentEvents)) {
            $lastTick = $upToTick ?? end($currentEvents)->getTick();
            $eras[] = $this->createEra($currentEraStart, $lastTick, $currentEvents);
        }

        return $eras;
    }

    private function isEraBreakpoint(ChronicleEvent $event, array $previousEvents): bool
    {
        // A breakpoint occurs if:
        // 1. A High severity event happens
        // 2. A specific "Era Transition" type event occurs
        // 3. (Future) A massive spike in Entropy is detected in payload
        
        if ($event->getSeverity()->rank() >= 4) { // Severity::CRITICAL
            return true;
        }

        // If many ticks have passed since last event, it might be a new era
        if (!empty($previousEvents)) {
            $lastEvent = end($previousEvents);
            if (($event->getTick() - $lastEvent->getTick()) > 1000) {
                return true;
            }
        }

        return false;
    }

    private function createEra(int $startTick, int $endTick, array $eraEvents): HistoricalEra
    {
        $theme = $this->determineTheme($eraEvents);
        $name = $this->generateEraName($theme, $eraEvents);
        
        return new HistoricalEra(
            name: $name,
            startTick: $startTick,
            endTick: $endTick,
            theme: $theme,
            description: $this->generateDescription($name, $theme, $eraEvents)
        );
    }

    private function determineTheme(array $events): string
    {
        $severities = array_map(fn($e) => $e->getSeverity()->rank(), $events);
        $maxSeverity = !empty($severities) ? max($severities) : 1;

        if ($maxSeverity >= 4) {
            return 'collapse';
        }

        // Count event types (mock logic for now)
        return 'prosperity';
    }

    private function generateEraName(string $theme, array $events): string
    {
        // Generative logic: combine theme with a random descriptor
        $descriptors = [
            'collapse'   => ['Tro tàn', 'Hỗn mang', 'Sụp đổ', 'Đoạn tuyệt'],
            'prosperity' => ['Hoàng kim', 'Ánh sáng', 'Khởi sinh', 'Thếnh thăng'],
            'chaos'      => ['Bão tố', 'Vết rạn', 'Nghịch lý'],
        ];

        $list = $descriptors[$theme] ?? ['Vô danh'];
        $suffix = $list[array_rand($list)];
        
        return "Kỷ nguyên $suffix";
    }

    private function generateDescription(string $name, string $theme, array $events): string
    {
        $count = count($events);
        return "Giai đoạn $name được đánh dấu bởi sự kiện trọng tâm với $count biến cố lớn, mang âm hưởng của sự $theme.";
    }
}
