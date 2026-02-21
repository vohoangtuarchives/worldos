<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmos\Service;

use Tuzy\Domain\Cosmos\Contracts\Objective;
use Tuzy\Domain\Cosmos\ValueObject\FitnessVector;
use Tuzy\Domain\Evolution\Entity\Universe;

/**
 * Engine điều phối các Objective.
 * Cho phép MetaCycle truy cập các tiêu chí đánh giá và thực hiện Meta-Mutation.
 */
final class ObjectiveEngine
{
    private Objective $currentObjective;
    private array $history = [];

    public function __construct(Objective $initialObjective)
    {
        $this->currentObjective = $initialObjective;
    }

    public function evaluate(Universe $universe, array $civilizations): FitnessVector
    {
        return $this->currentObjective->evaluate($universe, $civilizations);
    }

    /**
     * Thay đổi mục tiêu tối ưu (Meta-Mutation).
     */
    public function setObjective(Objective $objective): void
    {
        $this->history[] = $this->currentObjective->getName();
        $this->currentObjective = $objective;
    }

    public function getCurrentObjective(): Objective
    {
        return $this->currentObjective;
    }

    public function getHistory(): array
    {
        return $this->history;
    }
}
