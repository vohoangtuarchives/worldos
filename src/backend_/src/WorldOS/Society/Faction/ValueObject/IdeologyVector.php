<?php

declare(strict_types=1);

namespace WorldOS\Society\Faction\ValueObject;

/**
 * Faction ideology dimensions (0.0–1.0 each).
 */
readonly class IdeologyVector
{
    public function __construct(
        public float $militarism = 0.5,
        public float $spiritualism = 0.5,
        public float $expansionism = 0.5,
        public float $collectivism = 0.5,
        public float $purity = 0.5,
    ) {
    }

    public static function random(): self
    {
        return new self(
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
            mt_rand(0, 100) / 100,
        );
    }

    public function toArray(): array
    {
        return [
            'militarism' => $this->militarism,
            'spiritualism' => $this->spiritualism,
            'expansionism' => $this->expansionism,
            'collectivism' => $this->collectivism,
            'purity' => $this->purity,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['militarism'] ?? 0.5),
            (float) ($data['spiritualism'] ?? 0.5),
            (float) ($data['expansionism'] ?? 0.5),
            (float) ($data['collectivism'] ?? 0.5),
            (float) ($data['purity'] ?? 0.5),
        );
    }

    public function mutate(float $variance = 0.05): self
    {
        return new self(
            $this->clamp($this->militarism + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($this->spiritualism + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($this->expansionism + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($this->collectivism + (mt_rand(-100, 100) / 100) * $variance),
            $this->clamp($this->purity + (mt_rand(-100, 100) / 100) * $variance),
        );
    }

    /**
     * Ideological Drift: Reacts to physical pressure (StateVector) and cultural state.
     */
    public function drift(
        \WorldOS\Simulation\Domain\Engine\ValueObject\StateVector $physics,
        \WorldOS\Society\Culture\ValueObject\CulturalVector $culture,
        float $substrateMutationIntensity = 1.0
    ): self {
        // High Entropy -> Militarism & Expansionism++
        // High Stability -> Collectivism++
        // High Aesthetic Density -> Spiritualism++
        
        $entropy = $physics->get(\WorldOS\Simulation\Domain\Engine\ValueObject\StateVector::DIMENSION_ENTROPY);
        $stability = 1.0 - $entropy;

        // Drift speed is reduced by tradition rigidity
        $plasticity = 1.0 - $culture->traditionRigidity;
        $driftScale = 0.05 * $plasticity * $substrateMutationIntensity;

        return new self(
            $this->clamp($this->militarism + ($entropy > 0.6 ? 1 : -1) * $driftScale),
            $this->clamp($this->spiritualism + ($culture->aestheticDensity > 0.7 ? 1 : -1) * $driftScale),
            $this->clamp($this->expansionism + ($entropy > 0.8 ? 1 : -0.5) * $driftScale),
            $this->clamp($this->collectivism + ($stability > 0.7 ? 1 : -1) * $driftScale),
            $this->clamp($this->purity + ($culture->traditionRigidity > 0.8 ? 0.5 : -0.2) * $driftScale),
        );
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
