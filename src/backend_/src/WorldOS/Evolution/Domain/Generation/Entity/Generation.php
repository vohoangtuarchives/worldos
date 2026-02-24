<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Generation\Entity;

enum GenerationStatus: string
{
    case RUNNING = 'running';
    case EVALUATING = 'evaluating';
    case COMPLETED = 'completed';
}

final class Generation
{
    public function __construct(
        public readonly int $index,
        public readonly int $populationSize,
        public readonly GenerationStatus $status,
    ) {
    }
}
