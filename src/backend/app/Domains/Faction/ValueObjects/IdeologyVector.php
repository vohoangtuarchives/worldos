<?php

namespace App\Domains\Faction\ValueObjects;

class IdeologyVector
{
    public function __construct(
        public float $militarism,    // 0.0 - 1.0 (Pacifist - Warmonger)
        public float $spiritualism,  // 0.0 - 1.0 (Materialist - Fanatic)
        public float $expansionism,  // 0.0 - 1.0 (Isolationist - Imperialist)
        public float $collectivism,  // 0.0 - 1.0 (Individualist - Hivemind)
        public float $purity,        // 0.0 - 1.0 (Xenophile - Xenophobe)
    ) {}

    public static function random(): self
    {
        return new self(
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100),
            (mt_rand(0, 100) / 100)
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
            $data['militarism'] ?? 0.5,
            $data['spiritualism'] ?? 0.5,
            $data['expansionism'] ?? 0.5,
            $data['collectivism'] ?? 0.5,
            $data['purity'] ?? 0.5
        );
    }

    /**
     * Mutate ideology vector.
     */
    public function mutate(float $variance = 0.05): self
    {
        return new self(
            $this->mutateValue($this->militarism, $variance),
            $this->mutateValue($this->spiritualism, $variance),
            $this->mutateValue($this->expansionism, $variance),
            $this->mutateValue($this->collectivism, $variance),
            $this->mutateValue($this->purity, $variance)
        );
    }

    private function mutateValue(float $value, float $variance): float
    {
        $change = (mt_rand(-100, 100) / 100) * $variance;
        return max(0.0, min(1.0, $value + $change));
    }
}
