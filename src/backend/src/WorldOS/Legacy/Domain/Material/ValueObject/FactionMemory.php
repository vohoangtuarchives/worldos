<?php

namespace WorldOS\Legacy\Domain\Material\ValueObject;

class FactionMemory
{
    public function __construct(
        public float $successScore = 0.0,
        public float $warFatigue = 0.0,
        public float $mythBacklash = 0.0,
        public array $intentHistory = []
    ) {}

    public static function fresh(): self
    {
        return new self();
    }

    public function recordIntent(string $intent, bool $isSuccess): void
    {
        $this->intentHistory[] = [
            'intent' => $intent,
            'success' => $isSuccess,
            'timestamp' => time()
        ];
        
        // Keep only last 10 intents
        if (count($this->intentHistory) > 10) {
            array_shift($this->intentHistory);
        }
    }

    public function increaseSuccessScore(float $amount): void
    {
        $this->successScore = min(1.0, $this->successScore + $amount);
    }

    public function increaseWarFatigue(float $amount): void
    {
        $this->warFatigue = min(1.0, $this->warFatigue + $amount);
    }

    public function increaseMythBacklash(float $amount): void
    {
        $this->mythBacklash = min(1.0, $this->mythBacklash + $amount);
    }

    public function decayFatigue(float $rate = 0.1): void
    {
        $this->warFatigue = max(0.0, $this->warFatigue - ($this->warFatigue * $rate));
        $this->mythBacklash = max(0.0, $this->mythBacklash - ($this->mythBacklash * $rate));
    }
}
