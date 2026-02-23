<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

/**
 * LifeState
 * 
 * Represents the "Biology" layer of the world.
 * Crucial for determining if conditions allow for civilization re-seeding.
 */
final class LifeState
{
    public function __construct(
        public readonly float $complexity, // 0.0 (virus) to 1.0 (super-intelligence potential)
        public readonly float $diversity,  // Species count/niche filling
        public readonly float $biomass,    // Total organic volume
    ) {}

    public static function primordial(): self
    {
        return new self(0.05, 0.1, 0.1);
    }

    public function evolve(float $habitability, float $chaos, int $deltaYears): self
    {
        $dt = $deltaYears / 100.0;
        
        // Growth depends on habitability
        $growth = ($habitability - 0.3) * 0.05 * $dt;
        
        // Chaos increases diversity (mutations) but can damage complexity
        $newDiversity = $this->diversity + ($chaos * 0.02 * $dt);
        $newComplexity = $this->complexity + $growth - ($chaos * 0.01 * $dt);

        return new self(
            complexity: max(0.0, min(1.0, $newComplexity)),
            diversity: max(0.0, min(1.0, $newDiversity)),
            biomass: max(0.0, min(1.0, $this->biomass + $growth))
        );
    }

    public function getBiodiversityLabel(): string
    {
        if ($this->diversity < 0.1) return 'Vô sinh (Sterile)';
        if ($this->diversity < 0.3) return 'Vi sinh (Microbial)';
        if ($this->diversity < 0.5) return 'Đa bào sơ khai';
        if ($this->diversity < 0.7) return 'Phát triển';
        if ($this->diversity < 0.9) return 'Bùng nổ sinh học';
        
        return 'Siêu đa dạng (Hyper-diverse)';
    }

    public function toArray(): array
    {
        return [
            'complexity' => $this->complexity,
            'diversity' => $this->diversity,
            'biomass' => $this->biomass,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            complexity: (float) ($data['complexity'] ?? 0.0),
            diversity: (float) ($data['diversity'] ?? 0.0),
            biomass: (float) ($data['biomass'] ?? 0.0),
        );
    }
}
