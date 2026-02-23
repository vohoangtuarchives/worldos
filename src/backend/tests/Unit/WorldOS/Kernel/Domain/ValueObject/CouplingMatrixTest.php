<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Kernel\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WorldOS\Kernel\Domain\ValueObject\CouplingMatrix;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

final class CouplingMatrixTest extends TestCase
{
    // ------------------------------------------------------------------
    // Identity matrix
    // ------------------------------------------------------------------

    public function test_identity_matrix_multiply_preserves_vector(): void
    {
        $matrix = CouplingMatrix::identity(1.0); // no scaling
        $state  = StateVector::genesis();

        $result = $matrix->multiply($state);

        foreach ($state->all() as $key => $expected) {
            $this->assertEqualsWithDelta($expected, $result->get($key), 1e-10, "Dimension: {$key}");
        }
    }

    public function test_identity_matrix_with_scale_shrinks_values(): void
    {
        $scale  = 0.5;
        $matrix = CouplingMatrix::identity($scale);
        $state  = StateVector::genesis();

        $result = $matrix->multiply($state);

        foreach ($state->all() as $key => $expected) {
            $this->assertEqualsWithDelta($expected * $scale, $result->get($key), 1e-10, "Dimension: {$key}");
        }
    }

    // ------------------------------------------------------------------
    // Spectral radius
    // ------------------------------------------------------------------

    public function test_identity_matrix_spectral_radius_equals_scale(): void
    {
        $scale  = 0.98;
        $matrix = CouplingMatrix::identity($scale);
        $rho    = $matrix->getSpectralRadius();

        $this->assertEqualsWithDelta($scale, $rho, 0.001);
    }

    public function test_generate_stable_spectral_radius_is_bounded(): void
    {
        $matrix = CouplingMatrix::generateStable(0.98, 42);
        $rho    = $matrix->getSpectralRadius();

        // Power iteration gives an approximation. The matrix is designed with
        // diagonal ≈ 0.93–1.03 and off-diagonal ±0.05, so ρ stays near 1.0.
        // Hard stability enforcement is done by SpectralAnalyzer::assertStable().
        $this->assertLessThan(1.5, $rho, 'Generated matrix should be near-stable (ρ < 1.5)');
        $this->assertGreaterThan(0.5, $rho, 'Generated matrix should have non-trivial spectral radius');
    }

    // ------------------------------------------------------------------
    // Validation
    // ------------------------------------------------------------------

    public function test_from_array_throws_on_wrong_row_count(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Only 3 rows instead of 17
        CouplingMatrix::fromArray([[0.1, 0.2], [0.3, 0.4], [0.1, 0.0]]);
    }

    public function test_from_array_throws_on_wrong_col_count(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // 17 rows but first row has wrong columns
        $matrix = array_fill(0, 17, array_fill(0, 17, 0.0));
        $matrix[0] = [0.1]; // only 1 column
        CouplingMatrix::fromArray($matrix);
    }

    // ------------------------------------------------------------------
    // Spectral radius caching
    // ------------------------------------------------------------------

    public function test_spectral_radius_is_cached(): void
    {
        $matrix = CouplingMatrix::identity(0.95);

        $rho1 = $matrix->getSpectralRadius();
        $rho2 = $matrix->getSpectralRadius();

        $this->assertSame($rho1, $rho2);
    }

    // ------------------------------------------------------------------
    // Cross-dimension coupling
    // ------------------------------------------------------------------

    public function test_non_identity_matrix_creates_cross_dimension_effect(): void
    {
        // Build a matrix where row 0 has strong coupling FROM dimension 1
        // This means output[0] is influenced by input[1]
        $n      = 17;
        $raw    = array_fill(0, $n, array_fill(0, $n, 0.0));
        $raw[0][0] = 0.0; // no self-regression
        $raw[0][1] = 1.0; // output[0] = 1.0 × input[1]
        for ($i = 1; $i < $n; $i++) {
            $raw[$i][$i] = 0.98; // normal diagonal for other rows
        }

        $matrix = CouplingMatrix::fromArray($raw);

        $keys   = array_keys(StateVector::DEFAULT_DIMENSIONS);
        $input  = StateVector::genesis();

        $result = $matrix->multiply($input);

        // output[dim_0] should equal input[dim_1]
        $this->assertEqualsWithDelta(
            $input->get($keys[1]),
            $result->get($keys[0]),
            1e-10,
            'Cross-dimension coupling must propagate influence correctly'
        );
    }
}
