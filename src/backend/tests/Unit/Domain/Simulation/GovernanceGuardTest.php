<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Simulation;

use App\Domain\Simulation\Regimes\RegimeFactory;
use App\Domain\Simulation\Fields\ScarMemory;
use App\Domain\Multiverse\BranchManager;
use App\Domain\Multiverse\RegimeTransitionSignature;
use PHPUnit\Framework\TestCase;

/**
 * GovernanceGuardTest — Kiểm tra các Invariants của Hệ thống (Phần PHP).
 *
 * Lưu ý: Kiểm tra Rust GovernanceGuard thực sự yêu cầu Rust server đang chạy.
 * File này test các lớp PHP kiểm tra an toàn: ScarMemory, BranchManager, RegimeParameters.
 */
final class GovernanceGuardTest extends TestCase
{
    /**
     * Test: ScarMemory ghi nhận collapse và tích lũy Scar đúng cách.
     */
    public function test_scar_memory_accumulates_on_collapse(): void
    {
        $scar = new ScarMemory(universeId: 'universe-1');

        $this->assertSame(0.0, $scar->getMagnitude());

        $scar->recordCollapse(0.6); // Collapse mức độ 0.6

        $this->assertGreaterThan(0.0, $scar->getMagnitude());
        $this->assertSame(1, $scar->getCollapseCount());
    }

    /**
     * Test: ScarMemory không bao giờ vượt quá 1.0 (boundary invariant).
     */
    public function test_scar_magnitude_never_exceeds_one(): void
    {
        $scar = new ScarMemory(universeId: 'universe-1');

        for ($i = 0; $i < 20; $i++) {
            $scar->recordCollapse(1.0);
        }

        $this->assertLessThanOrEqual(1.0, $scar->getMagnitude(), 'Scar magnitude must never exceed 1.0');
    }

    /**
     * Test: ScarMemory làm suy giảm Energy Cap — hệ số phải < 1.0 khi Scar > 0.
     */
    public function test_scar_reduces_energy_cap_ratio(): void
    {
        $scar = new ScarMemory(universeId: 'universe-1');
        $scar->recordCollapse(0.5);

        $ratio = $scar->effectiveEnergyCapRatio();

        $this->assertLessThan(1.0, $ratio, 'Scar must reduce Energy Cap ratio');
        $this->assertGreaterThan(0.0, $ratio, 'Energy Cap ratio must remain positive');
    }

    /**
     * Test: ScarMemory PTSD Factor giảm dần khi Scar tăng (văn minh bị ức chế).
     */
    public function test_ptsd_factor_decreases_with_scar_severity(): void
    {
        $traumatizedScar = new ScarMemory(universeId: 'universe-heavy');
        $traumatizedScar->recordCollapse(0.8);
        $traumatizedScar->recordCollapse(0.7);

        $healthyScar = new ScarMemory(universeId: 'universe-light');
        $healthyScar->recordCollapse(0.1);

        $this->assertLessThan(
            $healthyScar->computePtsdFactor(),
            $traumatizedScar->computePtsdFactor(),
            'Heavier scar must result in lower PTSD Factor (stronger Exploration Inhibition)'
        );
    }

    /**
     * Test: BranchManager tạo tham số đột biến có thể phân biệt được với cha.
     */
    public function test_branch_manager_creates_diverse_child(): void
    {
        $parentParams = [
            'alpha'        => 0.25,
            'lambda'       => 0.0,
            'eta'          => 0.30,
            'beta'         => 0.01,
            'delta_target' => 0.08,
            'gamma_cap'    => 1.5,
        ];

        $rts = new RegimeTransitionSignature(
            transitionMatrix:       array_fill(0, 25, 0.2),
            dwellVector:            [0.1, 0.5, 0.2, 0.15, 0.05],  // R2 dominant → Type A
            oscillationIndex:       2,
            collapsePrecursorHash:  hash('sha256', 'test'),
            collapseType:           'A',
            regimeEntropy:          0.3,
        );

        $manager = new BranchManager();
        $childParams = $manager->computeMutatedParams($parentParams, $rts, 0.0);

        // Type A phải tăng η
        $this->assertGreaterThan($parentParams['eta'], $childParams['eta'],
            'Type A collapse should increase eta (damping)');

        // Phải đủ đa dạng
        $this->assertTrue(
            $manager->isSufficientlyDiverse($parentParams, $childParams),
            'Child params must be sufficiently diverse from parent (ε-separated)'
        );
    }

    /**
     * Test: BranchManager prune Universe không đa dạng đủ.
     */
    public function test_branch_manager_prunes_low_entropy_universe(): void
    {
        $manager = new BranchManager();

        // Universe nhàm chán (entropy thấp) → should prune
        $this->assertTrue($manager->shouldPrune(0.1));

        // Universe đa dạng (entropy cao) → không prune
        $this->assertFalse($manager->shouldPrune(0.8));
    }

    /**
     * Test: Tất cả Regimes phải tuân thủ Invariant #3: η ≤ 0.50 (không triệt tiêu quá mức).
     */
    public function test_all_regimes_eta_within_safe_bounds(): void
    {
        $regimes = [
            RegimeFactory::stableCivilization(),
            RegimeFactory::innovationSurge(),
            RegimeFactory::polarization(),
            RegimeFactory::turbulence(),
            RegimeFactory::collapseBasin(),
        ];

        foreach ($regimes as $regime) {
            $this->assertGreaterThan(0.0, $regime->eta, "{$regime->name}: η must be > 0");
            $this->assertLessThanOrEqual(0.50, $regime->eta, "{$regime->name}: η must be ≤ 0.50");
        }
    }
}
