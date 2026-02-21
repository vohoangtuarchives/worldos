<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Entity;

use Tuzy\Domain\Shared\Entity\AggregateRoot;

class LawGenome extends AggregateRoot
{
    private float $minEntropy;
    private float $maxEntropy;
    private float $baseMutationRate;
    private float $interactionGain;

    public function __construct(
        string $id,
        float $minEntropy = 0.3,
        float $maxEntropy = 0.8,
        float $baseMutationRate = 0.05,
        float $interactionGain = 1.0
    ) {
        parent::__construct($id);
        $this->minEntropy = $minEntropy;
        $this->maxEntropy = $maxEntropy;
        $this->baseMutationRate = $baseMutationRate;
        $this->interactionGain = $interactionGain;
    }

    public function getMinEntropy(): float
    {
        return $this->minEntropy;
    }

    public function getMaxEntropy(): float
    {
        return $this->maxEntropy;
    }

    public function getBaseMutationRate(): float
    {
        return $this->baseMutationRate;
    }

    public function getInteractionGain(): float
    {
        return $this->interactionGain;
    }
    
    // Immutable properties
}
