<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Collections;

use App\Domains\Intelligence\ValueObjects\IntelligenceReport;
use App\Domains\Intelligence\ValueObjects\IntelligenceType;
use Illuminate\Support\Collection;

final class IntelligenceCollection
{
    /** @var IntelligenceReport[] */
    private array $reports = [];
    private readonly string $worldId;

    public function __construct(string $worldId)
    {
        $this->worldId = $worldId;
    }

    public function add(IntelligenceReport $report): void
    {
        $this->reports[] = $report;
    }

    public function count(): int
    {
        return count($this->reports);
    }

    public function isEmpty(): bool
    {
        return empty($this->reports);
    }

    public function worldId(): string
    {
        return $this->worldId;
    }

    public function all(): array
    {
        return $this->reports;
    }

    public function getByType(IntelligenceType $type): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->reports as $report) {
            if ($report->type === $type) {
                $filtered->add($report);
            }
        }

        return $filtered;
    }

    public function getReliable(): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->reports as $report) {
            if ($report->isReliable()) {
                $filtered->add($report);
            }
        }

        return $filtered;
    }

    public function getRecent(int $maxAge = 10): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->reports as $report) {
            if ($report->isRecent($maxAge)) {
                $filtered->add($report);
            }
        }

        return $filtered;
    }

    public function getActionable(): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->reports as $report) {
            if ($report->type->isActionable()) {
                $filtered->add($report);
            }
        }

        return $filtered;
    }

    public function getBySource(string $sourceType, string $sourceId): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->reports as $report) {
            if ($report->source->type === $sourceType && $report->source->id === $sourceId) {
                $filtered->add($report);
            }
        }

        return $filtered;
    }

    public function getHighUrgency(): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->reports as $report) {
            if ($report->getUrgency() === 'high') {
                $filtered->add($report);
            }
        }

        return $filtered;
    }

    public function getSourceBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->reports as $report) {
            $sourceType = $report->source->type;
            $breakdown[$sourceType] = ($breakdown[$sourceType] ?? 0) + 1;
        }

        return $breakdown;
    }

    public function getTypeBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->reports as $report) {
            $type = $report->type->value;
            $breakdown[$type] = ($breakdown[$type] ?? 0) + 1;
        }

        return $breakdown;
    }

    public function getAverageAccuracy(): float
    {
        if (empty($this->reports)) {
            return 0.0;
        }

        $totalAccuracy = array_sum(array_map(fn($r) => $r->accuracy, $this->reports));
        
        return $totalAccuracy / count($this->reports);
    }

    public function getAverageReliability(): float
    {
        if (empty($this->reports)) {
            return 0.0;
        }

        $totalReliability = array_sum(array_map(fn($r) => $r->source->reliability, $this->reports));
        
        return $totalReliability / count($this->reports);
    }

    public function ageIntelligence(): void
    {
        $this->reports = array_map(fn($r) => $r->withAge($r->age + 1), $this->reports);
    }

    public function decayAccuracy(float $decayRate): void
    {
        $this->reports = array_map(fn($r) => $r->withDecayedAccuracy($decayRate), $this->reports);
    }

    public function removeOldIntelligence(int $maxAge): void
    {
        $this->reports = array_filter($this->reports, fn($r) => !$r->isExpired($maxAge));
        $this->reports = array_values($this->reports); // Re-index array
    }

    public function removeLowAccuracy(float $minAccuracy = 0.3): void
    {
        $this->reports = array_filter($this->reports, fn($r) => $r->accuracy >= $minAccuracy);
        $this->reports = array_values($this->reports);
    }

    public function sortByPriority(): self
    {
        $sorted = new self($this->worldId);
        
        $sortedReports = $this->reports;
        usort($sortedReports, fn($a, $b) => $a->type->getPriority() <=> $b->type->getPriority());
        
        foreach ($sortedReports as $report) {
            $sorted->add($report);
        }

        return $sorted;
    }

    public function sortByAccuracy(): self
    {
        $sorted = new self($this->worldId);
        
        $sortedReports = $this->reports;
        usort($sortedReports, fn($a, $b) => $b->accuracy <=> $a->accuracy);
        
        foreach ($sortedReports as $report) {
            $sorted->add($report);
        }

        return $sorted;
    }

    public function sortByUrgency(): self
    {
        $sorted = new self($this->worldId);
        
        $urgencyOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        
        $sortedReports = $this->reports;
        usort($sortedReports, fn($a, $b) => 
            $urgencyOrder[$a->getUrgency()] <=> $urgencyOrder[$b->getUrgency()]
        );
        
        foreach ($sortedReports as $report) {
            $sorted->add($report);
        }

        return $sorted;
    }

    public function limit(int $count): self
    {
        $limited = new self($this->worldId);
        
        foreach (array_slice($this->reports, 0, $count) as $report) {
            $limited->add($report);
        }

        return $limited;
    }

    public function getSummary(): array
    {
        return [
            'world_id' => $this->worldId,
            'total_reports' => $this->count(),
            'source_breakdown' => $this->getSourceBreakdown(),
            'type_breakdown' => $this->getTypeBreakdown(),
            'average_accuracy' => $this->getAverageAccuracy(),
            'average_reliability' => $this->getAverageReliability(),
            'reliable_count' => $this->getReliable()->count(),
            'recent_count' => $this->getRecent()->count(),
            'actionable_count' => $this->getActionable()->count(),
            'high_urgency_count' => $this->getHighUrgency()->count(),
        ];
    }

    public function toArray(): array
    {
        return array_map(fn($r) => $r->toArray(), $this->reports);
    }

    public function merge(self $other): self
    {
        $merged = new self($this->worldId);
        
        foreach ($this->reports as $report) {
            $merged->add($report);
        }
        
        foreach ($other->reports as $report) {
            $merged->add($report);
        }

        return $merged;
    }
}
