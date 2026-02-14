<?php

declare(strict_types=1);

namespace App\Domains\Character\ValueObjects;

final readonly class NarrativeWeight
{
    private function __construct(
        private float $storyImportance,
        private float $plotCriticality,
        private float $emotionalImpact,
        private float $completionPercentage,
        private bool $isMainCharacter,
    ) {
        $this->validateRanges();
    }

    public static function minor(): self
    {
        return new self(0.1, 0.0, 0.1, 0.0, false);
    }

    public static function supporting(): self
    {
        return new self(0.4, 0.2, 0.3, 0.2, false);
    }

    public static function main(): self
    {
        return new self(0.8, 0.7, 0.8, 0.0, true);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['story_importance'] ?? 0.0,
            $data['plot_criticality'] ?? 0.0,
            $data['emotional_impact'] ?? 0.0,
            $data['completion_percentage'] ?? 0.0,
            $data['is_main_character'] ?? false,
        );
    }

    public function storyImportance(): float
    {
        return $this->storyImportance;
    }

    public function plotCriticality(): float
    {
        return $this->plotCriticality;
    }

    public function emotionalImpact(): float
    {
        return $this->emotionalImpact;
    }

    public function completionPercentage(): float
    {
        return $this->completionPercentage;
    }

    public function isMainCharacter(): bool
    {
        return $this->isMainCharacter;
    }

    public function protectionFactor(): float
    {
        return ($this->storyImportance * 0.3)
            + ($this->plotCriticality * 0.4)
            + ($this->emotionalImpact * 0.2)
            + ($this->completionPercentage * 0.1);
    }

    public function withCompletion(float $percentage): self
    {
        return new self(
            $this->storyImportance,
            $this->plotCriticality,
            $this->emotionalImpact,
            min(1.0, $percentage),
            $this->isMainCharacter,
        );
    }

    public function increaseImportance(float $delta): self
    {
        return new self(
            min(1.0, $this->storyImportance + $delta),
            $this->plotCriticality,
            $this->emotionalImpact,
            $this->completionPercentage,
            $this->isMainCharacter,
        );
    }

    public function toArray(): array
    {
        return [
            'story_importance' => $this->storyImportance,
            'plot_criticality' => $this->plotCriticality,
            'emotional_impact' => $this->emotionalImpact,
            'completion_percentage' => $this->completionPercentage,
            'is_main_character' => $this->isMainCharacter,
            'protection_factor' => $this->protectionFactor(),
        ];
    }

    private function validateRanges(): void
    {
        $factors = [
            'story_importance' => $this->storyImportance,
            'plot_criticality' => $this->plotCriticality,
            'emotional_impact' => $this->emotionalImpact,
            'completion_percentage' => $this->completionPercentage,
        ];

        foreach ($factors as $name => $value) {
            if ($value < 0.0 || $value > 1.0) {
                throw new \InvalidArgumentException("Narrative weight {$name} must be between 0 and 1, got {$value}");
            }
        }
    }
}
