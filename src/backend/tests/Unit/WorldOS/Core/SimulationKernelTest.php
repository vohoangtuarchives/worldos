<?php

namespace Tests\Unit\WorldOS\Core;

use PHPUnit\Framework\TestCase;
use WorldOS\Core\SimulationKernel;
use WorldOS\Core\ValueObject\CivilizationSnapshot;
use WorldOS\Core\ValueObject\LifecycleState;
use WorldOS\Core\ValueObject\SubstrateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Society\Culture\ValueObject\CulturalVector;
use WorldOS\Society\Faction\ValueObject\IdeologyVector;

class SimulationKernelTest extends TestCase
{
    private SimulationKernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new SimulationKernel();
    }

    public function test_kernel_is_deterministic(): void
    {
        $snapshot = new CivilizationSnapshot(
            'test-civ',
            StateVector::genesis(),
            IdeologyVector::random(),
            CulturalVector::default(),
            LifecycleState::Emerging,
            0,
            1.0
        );

        $substrate = new SubstrateVector(1.0, 1.0, 1.0, 1.0, 1.0);

        $result1 = $this->kernel->tick($snapshot, $substrate);
        $result2 = $this->kernel->tick($snapshot, $substrate);

        $this->assertEquals($result1->snapshot->toArray(), $result2->snapshot->toArray());
    }

    public function test_ideology_drifts_with_high_entropy(): void
    {
        // Setup high entropy state
        $physics = StateVector::genesis()->withDimension(StateVector::DIMENSION_ENTROPY, 0.9);
        
        $snapshot = new CivilizationSnapshot(
            'test-civ',
            $physics,
            new IdeologyVector(militarism: 0.1), // Low militarism
            CulturalVector::default(),
            LifecycleState::Emerging,
            0,
            1.0
        );

        $substrate = new SubstrateVector(1.0, 1.0, 1.0, 1.0, 1.0);

        $result = $this->kernel->tick($snapshot, $substrate);

        // Militarism should increase due to high entropy (drift logic implemented)
        $this->assertGreaterThan(0.1, $result->snapshot->ideology->militarism);
    }
}
