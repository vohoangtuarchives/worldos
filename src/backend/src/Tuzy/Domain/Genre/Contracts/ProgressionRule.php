<?php

namespace Tuzy\Domain\Genre\Contracts;

interface ProgressionRule
{
    /**
     * Defined stages of power.
     * e.g., ['Qi Refining', 'Foundation Establishment'...]
     */
    public function stages(): array;

    /**
     * Can a character skip stages?
     */
    public function canSkipStage(): bool;

    /**
     * Probability of death when advancing stages (e.g., Tribulation).
     * 0.0 - 1.0
     */
    public function deathRisk(): float;
}
