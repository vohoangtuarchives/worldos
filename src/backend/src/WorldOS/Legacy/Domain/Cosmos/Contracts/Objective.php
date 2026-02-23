<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmos\Contracts;

use WorldOS\Evolution\Domain\Legacy\Entity\Universe;
use WorldOS\Legacy\Domain\Cosmos\ValueObject\FitnessVector;

/**
 * Interface cho các mục tiêu chọn lọc (Selection Objectives).
 * Cho phép MetaCycle thay đổi tiêu chí đánh giá mà không thay đổi Engine.
 */
interface Objective
{
    /**
     * Đánh giá một vũ trụ và trả về vector fitness.
     * @param Universe $universe
     * @param array $civilizations Snapshot các văn minh hiện tại
     */
    public function evaluate(Universe $universe, array $civilizations): FitnessVector;

    /**
     * Tên định danh của Objective.
     */
    public function getName(): string;
}
