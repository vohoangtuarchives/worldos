<?php

namespace WorldOS\Legacy\Domain\Material\ValueObject;

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

    public function protectionFactor(): float
    {
        return ($this->storyImportance * 0.3)
            + ($this->plotCriticality * 0.4)
            + ($this->emotionalImpact * 0.2)
            + ($this->completionPercentage * 0.1);
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
    
    public function isMainCharacter(): bool
    {
        return $this->isMainCharacter;
    }
    
    public function completionPercentage(): float
    {
        return $this->completionPercentage;
    }
}
