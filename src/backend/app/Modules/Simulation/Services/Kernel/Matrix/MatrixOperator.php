<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Kernel\Matrix;

interface MatrixOperator
{
    /**
     * Get the dimension of the square operator matrix (n x n).
     */
    public function dimension(): int;

    /**
     * Lazyly retrieve a specific row based on the 0-indexed integer.
     * Prevents materializing the entire matrix in RAM for large dimensions.
     *
     * @param int $i
     * @return array<int, float>
     */
    public function getRow(int $i): array;

    /**
     * Multiply the entire matrix operator by a given state vector.
     * Required for Power Iteration estimating Spectral Radius.
     *
     * @param array<int, float> $vector
     * @return array<int, float>
     */
    public function multiplyVector(array $vector): array;
}
