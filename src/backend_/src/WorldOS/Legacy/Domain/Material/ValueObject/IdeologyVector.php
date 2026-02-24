<?php

namespace WorldOS\Legacy\Domain\Material\ValueObject;

readonly class IdeologyVector
{
    public function __construct(
        public float $militarism,
        public float $spiritualism,
        public float $expansionism,
        public float $collectivism,
        public float $purity
    ) {}

    public static function default(): self
    {
        return new self(0.5, 0.5, 0.5, 0.5, 0.5);
    }

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
