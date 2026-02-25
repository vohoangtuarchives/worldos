<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Kernel;

use RuntimeException;

final class KernelMathException extends RuntimeException
{
    public static function nonSquareMatrix(int $rows, int $cols): self
    {
        return new self("Matrix must be square. Given: {$rows}x{$cols}.");
    }

    public static function dimensionMismatch(int $expected, int $actual): self
    {
        return new self("Dimension mismatch. Expected: {$expected}, Got: {$actual}.");
    }

    public static function zeroNorm(): self
    {
        return new self("Vector norm is zero, scaling or vector iteration failed.");
    }
}
