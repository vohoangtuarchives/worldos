<?php

namespace Tuzy\Application\Genre\Signal;

use Tuzy\Domain\Genre\ValueObject\GenreProfile;

class SignalAggregator
{
    /** @var GenreSignal[] */
    private array $history = [];

    public function record(GenreSignal $signal): void
    {
        $this->history[] = $signal;
    }

    public function calculateProfile(): GenreProfile
    {
        $totals = [];
        foreach ($this->history as $signal) {
            foreach ($signal->impacts as $genre => $value) {
                $totals[$genre] = ($totals[$genre] ?? 0.0) + ($value * $signal->permanence);
            }
        }

        // Normalize weights (sum to 1.0) if needed, 
        // or just return as is if weights represent raw pressure.
        return new GenreProfile($totals);
    }
}
