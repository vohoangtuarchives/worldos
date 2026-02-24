<?php

declare(strict_types=1);

namespace WorldOS\Kernel\Domain\ValueObject;

use InvalidArgumentException;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * CouplingMatrix — Immutable 17×17 linear transition matrix for the V5 kernel.
 *
 * Models cross-dimensional influence: entropy affects stability,
 * stability affects power_density, etc.
 *
 * Core equation per tick:
 *   S(t+1) = A · S(t) + N(t)
 *
 * Stability invariant: spectral radius ρ(A) ≤ 1.0
 * (enforced via SpectralAnalyzer before the matrix is used at runtime)
 */
final class CouplingMatrix
{
    private const EXPECTED_SIZE = 17;

    /** @var float[][] column-major: $matrix[$row][$col] */
    private readonly array $matrix;

    /** @var float|null lazily-computed spectral radius */
    private ?float $cachedSpectralRadius = null;

    /** @var string[] dimension keys in stable order */
    private readonly array $dimensionKeys;

    /**
     * @param float[][] $matrix   17×17 float matrix, row-major
     * @param string[]  $dimensionKeys  Keys matching StateVector::DEFAULT_DIMENSIONS order
     */
    private function __construct(array $matrix, array $dimensionKeys)
    {
        $this->matrix        = $matrix;
        $this->dimensionKeys = $dimensionKeys;
    }

    // ------------------------------------------------------------------
    // Factories
    // ------------------------------------------------------------------

    /**
     * Create from explicit 17×17 row-major float array.
     *
     * @param float[][] $matrix
     */
    public static function fromArray(array $matrix): self
    {
        $n = self::EXPECTED_SIZE;

        if (count($matrix) !== $n) {
            throw new InvalidArgumentException(
                "CouplingMatrix requires {$n} rows, " . count($matrix) . " given."
            );
        }

        foreach ($matrix as $r => $row) {
            if (count($row) !== $n) {
                throw new InvalidArgumentException(
                    "Row {$r} requires {$n} columns, " . count($row) . " given."
                );
            }
        }

        return new self($matrix, array_keys(StateVector::DEFAULT_DIMENSIONS));
    }

    /**
     * Create a scaled identity matrix (diagonal = $scale, off-diagonal = 0).
     * Spectral radius = $scale. Useful for tests and baseline runs.
     */
    public static function identity(float $scale = 0.98): self
    {
        $n      = self::EXPECTED_SIZE;
        $matrix = [];

        for ($r = 0; $r < $n; $r++) {
            $row = array_fill(0, $n, 0.0);
            $row[$r] = $scale;
            $matrix[$r] = $row;
        }

        return new self($matrix, array_keys(StateVector::DEFAULT_DIMENSIONS));
    }

    /**
     * Generate a random stable matrix with spectral radius approx. $targetRadius.
     * Uses seeded PHP mt_rand for determinism.
     */
    public static function generateStable(float $targetRadius = 0.98, int $seed = 42): self
    {
        $n = self::EXPECTED_SIZE;

        mt_srand($seed);

        $matrix = [];
        for ($r = 0; $r < $n; $r++) {
            $row = [];
            for ($c = 0; $c < $n; $c++) {
                // Small off-diagonal, stronger diagonal
                $row[$c] = ($r === $c)
                    ? ($targetRadius - 0.05 + (mt_rand(0, 100) / 1000.0))
                    : ((mt_rand(-50, 50) / 1000.0));   // ±0.05 coupling
            }
            $matrix[$r] = $row;
        }

        return new self($matrix, array_keys(StateVector::DEFAULT_DIMENSIONS));
    }

    // ------------------------------------------------------------------
    // Core operation
    // ------------------------------------------------------------------

    /**
     * Matrix-vector multiply: returns A · S(t) as a new StateVector.
     * Output dimensions are keyed by the same keys as $state.
     */
    public function multiply(StateVector $state): StateVector
    {
        $vector = $state->toIndexedArray(); // ordered float[17]
        $keys   = $this->dimensionKeys;
        $n      = self::EXPECTED_SIZE;

        $result = [];
        for ($r = 0; $r < $n; $r++) {
            $sum = 0.0;
            for ($c = 0; $c < $n; $c++) {
                $sum += $this->matrix[$r][$c] * $vector[$c];
            }
            $result[$keys[$r]] = $sum;
        }

        return StateVector::fromArray($result);
    }

    // ------------------------------------------------------------------
    // Spectral radius (power iteration — lightweight, no external deps)
    // ------------------------------------------------------------------

    /**
     * Approximate spectral radius ρ(A) via power iteration.
     * Result is cached after first computation.
     */
    public function getSpectralRadius(): float
    {
        if ($this->cachedSpectralRadius !== null) {
            return $this->cachedSpectralRadius;
        }

        $this->cachedSpectralRadius = $this->powerIteration(100);

        return $this->cachedSpectralRadius;
    }

    private function powerIteration(int $steps): float
    {
        $n = self::EXPECTED_SIZE;

        // Start with a uniform vector
        $v = array_fill(0, $n, 1.0 / sqrt($n));

        $eigenvalue = 0.0;

        for ($iter = 0; $iter < $steps; $iter++) {
            // w = A · v
            $w = array_fill(0, $n, 0.0);
            for ($r = 0; $r < $n; $r++) {
                for ($c = 0; $c < $n; $c++) {
                    $w[$r] += $this->matrix[$r][$c] * $v[$c];
                }
            }

            // Rayleigh quotient: λ ≈ w·v / v·v
            $norm = 0.0;
            $dot  = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $norm += $w[$i] * $w[$i];
                $dot  += $w[$i] * $v[$i];
            }

            $norm = sqrt($norm);

            if ($norm < 1e-12) {
                break; // zero matrix
            }

            $eigenvalue = $dot;

            // Normalise
            for ($i = 0; $i < $n; $i++) {
                $v[$i] = $w[$i] / $norm;
            }
        }

        return abs($eigenvalue);
    }

    // ------------------------------------------------------------------
    // Serialization
    // ------------------------------------------------------------------

    /** @return float[][] */
    public function toArray(): array
    {
        return $this->matrix;
    }

    public function getDimensionKeys(): array
    {
        return $this->dimensionKeys;
    }
}
