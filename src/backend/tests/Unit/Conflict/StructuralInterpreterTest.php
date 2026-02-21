<?php

declare(strict_types=1);

namespace Tests\Unit\Conflict;

use App\Domains\Conflict\StructuralInterpreter;
use Tuzy\Domain\Conflict\ValueObject\ConflictSeed;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Mathematics\PressureAccumulationField;
use App\Domains\Cosmology\Mathematics\StressModel;
use PHPUnit\Framework\TestCase;

class StructuralInterpreterTest extends TestCase
{
    private StructuralInterpreter $interpreter;

    protected function setUp(): void
    {
        parent::setUp();
        $pressureField = new PressureAccumulationField();
        $stressModel = new StressModel($pressureField);
        $this->interpreter = new StructuralInterpreter($stressModel);
    }

    public function test_interpret_returns_empty_when_state_calm(): void
    {
        $state = WorldStateVector::create(
            0.8, 0.2, 0.8, 0.8, 0.5, 0.2,
            0.2, 0.1, 0.6, 0.7
        );
        $seeds = $this->interpreter->interpret($state, 0.2);
        $this->assertIsArray($seeds);
        $this->assertEmpty($seeds);
    }

    public function test_interpret_produces_class_struggle_when_high_inequality_low_legitimacy(): void
    {
        $state = WorldStateVector::create(
            0.5, 0.4, 0.4, 0.3, 0.5, 0.3,
            0.75, 0.2, 0.5, 0.4
        );
        $seeds = $this->interpreter->interpret($state, 0.3);
        $types = array_column(array_map(fn ($s) => ['type' => $s->type], $seeds), 'type');
        $this->assertContains(ConflictSeed::TYPE_CLASS_STRUGGLE, $types);
    }

    public function test_interpret_produces_rebellion_potential_when_trauma_and_low_legitimacy(): void
    {
        $state = WorldStateVector::create(
            0.4, 0.5, 0.4, 0.35, 0.4, 0.4,
            0.5, 0.6, 0.5, 0.4
        );
        $seeds = $this->interpreter->interpret($state, 0.4);
        $types = array_column(array_map(fn ($s) => ['type' => $s->type], $seeds), 'type');
        $this->assertContains(ConflictSeed::TYPE_REBELLION_POTENTIAL, $types);
    }

    public function test_interpret_from_state_computes_pressure(): void
    {
        $state = WorldStateVector::create(
            0.5, 0.5, 0.3, 0.3, 0.5, 0.3,
            0.7, 0.5, 0.8, 0.3
        );
        $seeds = $this->interpreter->interpretFromState($state);
        $this->assertIsArray($seeds);
        foreach ($seeds as $seed) {
            $this->assertInstanceOf(ConflictSeed::class, $seed);
            $this->assertGreaterThanOrEqual(0.0, $seed->intensity);
            $this->assertLessThanOrEqual(1.0, $seed->intensity);
        }
    }
}
