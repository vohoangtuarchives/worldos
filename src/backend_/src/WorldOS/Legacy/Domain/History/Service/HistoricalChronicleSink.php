<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\History\Service;

/**
 * Accumulates the highlights of a long-running epoch simulation IN MEMORY.
 * This prevents thrashing the Database with thousands of inserts per tick.
 * Only the finalized chronicle and key milestone events are flushed to storage for the frontend.
 */
class HistoricalChronicleSink
{
    private array $chronicle = [
        'total_years' => 0,
        'epoch_resets' => 0,
        'max_technological_level' => 0.0,
        'major_conflicts' => 0,
        'total_heroes_emerged' => 0,
        'milestones' => []
    ];

    public function recordYearPassed(): void
    {
        $this->chronicle['total_years']++;
    }

    public function recordEpochReset(int $year, string $reason): void
    {
        $this->chronicle['epoch_resets']++;
        $this->recordMilestone($year, 'EPOCH_RESET', $reason);
    }

    public function updateMaxTech(float $techLevel): void
    {
        if ($techLevel > $this->chronicle['max_technological_level']) {
            $this->chronicle['max_technological_level'] = $techLevel;
        }
    }

    public function recordMajorConflict(int $year, string $aggressor, string $defender): void
    {
        $this->chronicle['major_conflicts']++;
        $this->recordMilestone($year, 'MAJOR_CONFLICT', "$aggressor attacked $defender");
    }

    public function recordHeroEmerged(int $year, string $civId): void
    {
        $this->chronicle['total_heroes_emerged']++;
        $this->recordMilestone($year, 'HERO_EMERGENCE', "A great figure arose in $civId");
    }

    private function recordMilestone(int $year, string $type, string $description): void
    {
        $this->chronicle['milestones'][] = [
            'year' => $year,
            'type' => $type,
            'description' => $description
        ];
    }

    public function exportChronicle(): array
    {
        return $this->chronicle;
    }
}
