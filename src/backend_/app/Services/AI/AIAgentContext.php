<?php

namespace App\Services\AI;

class AIAgentContext
{
    private ?string $featureKey = null;

    public function set(?string $featureKey): void
    {
        $this->featureKey = $featureKey;
    }

    public function get(): ?string
    {
        return $this->featureKey;
    }

    public function runWith(string $featureKey, callable $callback): mixed
    {
        $previous = $this->featureKey;
        $this->featureKey = $featureKey;

        try {
            return $callback();
        } finally {
            $this->featureKey = $previous;
        }
    }
}
