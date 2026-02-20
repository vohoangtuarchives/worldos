<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Mathematics;

use InvalidArgumentException;

/**
 * DynamicalMatrixMath
 * 
 * Provides linear algebra utilities for the bifurcation-controlled dynamical system.
 */
class DynamicalMatrixMath
{
    /**
     * M x v
     */
    public static function multiplyMatrixVector(array $A, array $v): array
    {
        $n = count($A);
        $result = array_fill(0, $n, 0.0);

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $result[$i] += $A[$i][$j] * $v[$j];
            }
        }

        return $result;
    }

    /**
     * v1 . v2
     */
    public static function dotProduct(array $v1, array $v2): float
    {
        $sum = 0.0;
        $n = count($v1);
        for ($i = 0; $i < $n; $i++) {
            $sum += $v1[$i] * $v2[$i];
        }
        return $sum;
    }

    /**
     * ||v||
     */
    public static function vectorNorm(array $v): float
    {
        return sqrt(self::dotProduct($v, $v));
    }

    /**
     * Estimate spectral radius (largest absolute eigenvalue) using Power Iteration.
     * ρ(A) = lim(k->∞) (v_k . A v_k) / (v_k . v_k)
     */
    public static function spectralRadius(array $A, int $iterations = 50): float
    {
        $n = count($A);
        if ($n === 0) return 0.0;

        $v = array_fill(0, $n, 1.0); // Initial guess

        for ($k = 0; $k < $iterations; $k++) {
            $v_new = self::multiplyMatrixVector($A, $v);
            $norm = self::vectorNorm($v_new);
            
            if ($norm == 0) {
                return 0.0;
            }

            for ($i = 0; $i < $n; $i++) {
                $v[$i] = $v_new[$i] / $norm;
            }
        }

        $Av = self::multiplyMatrixVector($A, $v);
        return self::dotProduct($v, $Av);
    }

    /**
     * M * scalar
     */
    public static function scaleMatrix(array $A, float $scalar): array
    {
        $n = count($A);
        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $result[$i][$j] = $A[$i][$j] * $scalar;
            }
        }
        return $result;
    }

    /**
     * A + B
     */
    public static function addMatrices(array $A, array $B): array
    {
        $n = count($A);
        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $result[$i][$j] = $A[$i][$j] + $B[$i][$j];
            }
        }
        return $result;
    }

    /**
     * I
     */
    public static function identityMatrix(int $n): array
    {
        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[$i] = array_fill(0, $n, 0.0);
            $result[$i][$i] = 1.0;
        }
        return $result;
    }

    /**
     * Thêm một ma trận đơn vị vào ma trận A (Ví dụ: I + A)
     */
    public static function identityPlus(array $A): array
    {
        $n = count($A);
        $I = self::identityMatrix($n);
        return self::addMatrices($I, $A);
    }
}
