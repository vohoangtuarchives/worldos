<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Kernel\Matrix;

use App\Modules\Simulation\Services\Kernel\KernelMathException;

/**
 * Xây dựng ma trận Jacobian J từ các thành phần cấu trúc.
 *
 * J = I + α[(A − I) − λL − ηI]
 *
 * Implements MatrixOperator để cho phép lazy row access
 * mà không cần materialize toàn bộ ma trận trong RAM.
 */
final class KernelMatrixBuilder implements MatrixOperator
{
    private int $n;

    /** @var array<int, array<int, float>> Row-stochastic matrix A */
    private array $A;

    /** @var array<int, array<int, float>> Graph Laplacian L (symmetric PSD) */
    private array $L;

    private float $alpha;
    private float $lambda;
    private float $eta;

    /**
     * @param array<int, array<int, float>> $A Row-stochastic matrix
     * @param array<int, array<int, float>> $L Graph Laplacian (symmetric, PSD). Pass empty array if no multi-region.
     * @param float $alpha Damping step size (0 < α < 1)
     * @param float $lambda Diffusion coefficient (λ ≥ 0)
     * @param float $eta Intrinsic damping (η > 0, MANDATORY)
     */
    public function __construct(
        array $A,
        array $L,
        float $alpha,
        float $lambda,
        float $eta
    ) {
        $this->n = count($A);

        if ($this->n === 0) {
            throw KernelMathException::nonSquareMatrix(0, 0);
        }

        $this->A = $A;
        $this->L = $L;
        $this->alpha = $alpha;
        $this->lambda = $lambda;
        $this->eta = $eta;
    }

    public function dimension(): int
    {
        return $this->n;
    }

    /**
     * Compute row i of J lazily.
     * J_ij = I_ij + α * [(A_ij - I_ij) - λ*L_ij - η*I_ij]
     * Simplify: J_ij = (1 - α - αη)*I_ij + α*A_ij - αλ*L_ij
     *           J_ii = (1 - α - αη) + α*A_ii - αλ*L_ii   (diagonal)
     *           J_ij = α*A_ij - αλ*L_ij                   (off-diagonal)
     */
    public function getRow(int $i): array
    {
        $row = [];
        $hasL = !empty($this->L);

        for ($j = 0; $j < $this->n; $j++) {
            $aVal = $this->A[$i][$j] ?? 0.0;
            $lVal = $hasL ? ($this->L[$i][$j] ?? 0.0) : 0.0;

            if ($i === $j) {
                // Diagonal: (1 - α(1 + η)) + α*A_ii - αλ*L_ii
                $row[$j] = (1.0 - $this->alpha * (1.0 + $this->eta))
                         + $this->alpha * $aVal
                         - $this->alpha * $this->lambda * $lVal;
            } else {
                // Off-diagonal: α*A_ij - αλ*L_ij
                $row[$j] = $this->alpha * $aVal
                         - $this->alpha * $this->lambda * $lVal;
            }
        }

        return $row;
    }

    /**
     * Matrix-vector multiply: result = J * vector (O(n²))
     */
    public function multiplyVector(array $vector): array
    {
        if (count($vector) !== $this->n) {
            throw KernelMathException::dimensionMismatch($this->n, count($vector));
        }

        $result = [];

        for ($i = 0; $i < $this->n; $i++) {
            $row = $this->getRow($i);
            $sum = 0.0;

            for ($j = 0; $j < $this->n; $j++) {
                $sum += $row[$j] * $vector[$j];
            }

            $result[$i] = $sum;
        }

        return $result;
    }
}
