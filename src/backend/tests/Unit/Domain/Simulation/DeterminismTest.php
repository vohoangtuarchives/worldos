<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Simulation;

use App\Domain\Simulation\Regimes\RegimeFactory;
use App\Domain\Simulation\Regimes\RegimeParameters;
use App\Domain\Simulation\Regimes\RegimeResolver;
use App\Modules\Simulation\Services\Kernel\MathCore;
use PHPUnit\Framework\TestCase;

/**
 * DeterminismTest — Kiểm tra tính Tất Định Tuyệt Đối của Kernel (AXIOM 1).
 *
 * Nguyên tắc: "Same seed, same config → identical output, always."
 * (WorldOS 1.0.1 §4 — Invariant #10: Tick Determinism)
 *
 * Không cần Rust Engine — test MathCore PHP và RegimeFactory thuần túy.
 */
final class DeterminismTest extends TestCase
{
    private MathCore $mathCore;

    protected function setUp(): void
    {
        $this->mathCore = new MathCore();
    }

    /**
     * Test: 2 lần chạy cùng Regime R1, cùng state vector x(0) phải cho
     * cùng x(1) xuất đến từng chữ số thập phân.
     */
    public function test_identical_runs_produce_identical_state(): void
    {
        $regime = RegimeFactory::stableCivilization();
        $x0     = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6];
        $u0     = [0.0, 0.0, 0.0, 0.0, 0.0, 0.0];
        $A      = $this->flatToMatrix($regime->aMatrix, $regime->dimension);
        $L      = $this->zeroMatrix($regime->dimension);

        // Chạy lần 1
        $x1a = $this->mathCore->step($x0, $u0, $A, $L, $regime->alpha, $regime->lambda, $regime->eta, $regime->beta);

        // Chạy lần 2 — cùng đầu vào
        $x1b = $this->mathCore->step($x0, $u0, $A, $L, $regime->alpha, $regime->lambda, $regime->eta, $regime->beta);

        // Phải hoàn toàn giống nhau (bit-for-bit đối với float64)
        $this->assertSame($x1a, $x1b, 'Determinism violated: same input gave different output');
    }

    /**
     * Test: Chạy 50 ticks liên tiếp, 2 runs phải có cùng Hash Chain cuối.
     */
    public function test_hash_chain_determinism_over_multiple_ticks(): void
    {
        $regime = RegimeFactory::stableCivilization();
        $x      = [0.5, -0.3, 0.8, -0.1, 0.2, -0.5];
        $u      = array_fill(0, 6, 0.0);
        $A      = $this->flatToMatrix($regime->aMatrix, $regime->dimension);
        $L      = $this->zeroMatrix($regime->dimension);

        // Run 1
        $stateChain1 = $this->runChain($x, $u, $A, $L, $regime, 50);

        // Run 2 — cùng điều kiện ban đầu
        $stateChain2 = $this->runChain($x, $u, $A, $L, $regime, 50);

        // Tính hash chain từ 2 lần run
        $hash1 = $this->computeChainHash($stateChain1);
        $hash2 = $this->computeChainHash($stateChain2);

        $this->assertSame($hash1, $hash2, "Hash chain mismatch after 50 ticks — Determinism violated");
    }

    /**
     * Test: Mỗi Regime phải có η > 0 (Invariant #2: Intrinsic Damping Mandatory).
     */
    public function test_all_regimes_have_positive_intrinsic_damping(): void
    {
        $regimes = [
            RegimeFactory::stableCivilization(),
            RegimeFactory::innovationSurge(),
            RegimeFactory::polarization(),
            RegimeFactory::turbulence(),
            RegimeFactory::collapseBasin(),
        ];

        foreach ($regimes as $regime) {
            $this->assertGreaterThan(0.0, $regime->eta,
                "Regime {$regime->name} violates Invariant #2: η must be > 0");
        }
    }

    /**
     * Test: RegimeFactory::fromName() phải resolve đúng
     */
    public function test_regime_factory_resolves_by_name(): void
    {
        $this->assertSame('R1_STABLE_CIVILIZATION', RegimeFactory::fromName('R1')->name);
        $this->assertSame('R2_INNOVATION_SURGE',    RegimeFactory::fromName('R2')->name);
        $this->assertSame('R3_POLARIZATION',        RegimeFactory::fromName('R3')->name);
        $this->assertSame('R4_TURBULENCE',          RegimeFactory::fromName('R4')->name);
        $this->assertSame('R5_COLLAPSE_BASIN',      RegimeFactory::fromName('R5')->name);
    }

    /**
     * Test: RegimeResolver phải phát hiện chuyển pha Innovation khi Innovation > 0.65.
     */
    public function test_regime_resolver_detects_innovation_surge(): void
    {
        $resolver = new RegimeResolver();

        // Observable state: Innovation cao (> 0.65)
        $observable = [0.4, 0.5, 0.70, 0.5, 0.3, 0.2];

        $resolved = $resolver->resolve($observable, 'R1');

        $this->assertSame('R2', $resolved, 'RegimeResolver should detect Innovation Surge when innovation > 0.65');
    }

    /**
     * Test: Collapse Basin phải được phát hiện khi Order và Cohesion đồng thời thấp.
     */
    public function test_regime_resolver_detects_collapse_basin(): void
    {
        $resolver = new RegimeResolver();

        // Observable state: Order < 0.20, Cohesion < 0.15 → Collapse
        $observable = [0.9, 0.15, 0.2, 0.10, 0.8, 0.7];

        $resolved = $resolver->resolve($observable, 'R4');

        $this->assertSame('R5', $resolved, 'RegimeResolver should detect Collapse Basin when order < 0.20 and cohesion < 0.15');
    }

    // --- Helpers ---

    private function runChain(array $x, array $u, array $A, array $L, RegimeParameters $regime, int $ticks): array
    {
        $chain = [$x];
        for ($i = 0; $i < $ticks; $i++) {
            $x = $this->mathCore->step($x, $u, $A, $L, $regime->alpha, $regime->lambda, $regime->eta, $regime->beta);
            $chain[] = $x;
        }
        return $chain;
    }

    private function computeChainHash(array $chain): string
    {
        $prev = '0';
        foreach ($chain as $state) {
            $prev = hash('sha256', $prev . json_encode($state, JSON_PRESERVE_ZERO_FRACTION));
        }
        return $prev;
    }

    private function flatToMatrix(array $flat, int $n): array
    {
        $matrix = [];
        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = array_slice($flat, $i * $n, $n);
        }
        return $matrix;
    }

    private function zeroMatrix(int $n): array
    {
        return array_fill(0, $n, array_fill(0, $n, 0.0));
    }
}
