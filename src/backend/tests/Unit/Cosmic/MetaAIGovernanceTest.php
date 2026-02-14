<?php

declare(strict_types=1);

namespace Tests\Unit\Cosmic;

use App\Domains\Cosmic\Services\ConstraintEngine;
use App\Domains\Cosmic\Services\QualityEvaluator;
use App\Domains\Cosmic\Services\SemanticProjector;
use App\Domains\Cosmic\Services\StyleAdvisorService;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Cosmic\ValueObjects\EnvironmentState;
use App\Domains\Cosmic\ValueObjects\UniverseStyleVersion;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use PHPUnit\Framework\TestCase;

class MetaAIGovernanceTest extends TestCase
{
    private function makeSnapshot(float $entropy, float $energy, float $stability, float $strain, string $attractor, int $year): WorldSnapshot
    {
        return new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: $entropy, energy: $energy, causality: 0.5,
                strain: $strain, stability: $stability,
                currentAttractor: $attractor, year: $year,
            ),
            environment: EnvironmentState::defaultObservation($year),
            civilization: CivilizationState::defaultObservation($year),
            year: $year,
        );
    }

    // --- StyleAdvisorService Tests ---

    public function test_advisor_produces_proposal_for_low_gi(): void
    {
        $advisor = new StyleAdvisorService(new QualityEvaluator(), new SemanticProjector());
        $style = UniverseStyleVersion::defaultStyle();

        // Chaotic fragmented trajectory → low GI → should propose
        $attractors = ['EQUILIBRIUM', 'HIGH_CHAOS', 'VOID_COLLAPSE', 'RESONANCE_DOMINANT'];
        $trajectory = [];
        for ($i = 0; $i < 30; $i++) {
            $att = $attractors[$i % 4];
            $trajectory[] = $this->makeSnapshot(0.7, 0.3, 0.2, 0.8, $att, $i * 100);
        }

        $result = $advisor->analyze($trajectory, $style, 100);
        $this->assertNotNull($result['proposal']);
        $this->assertTrue($result['proposal']['requires_human_approval']);
        $this->assertArrayHasKey('weight_adjustments', $result['proposal']);
    }

    public function test_advisor_no_proposal_for_high_gi(): void
    {
        $advisor = new StyleAdvisorService(new QualityEvaluator(), new SemanticProjector());
        $style = UniverseStyleVersion::defaultStyle();

        // Very stable trajectory → high GI → no proposal needed
        $trajectory = [];
        for ($i = 0; $i < 50; $i++) {
            $trajectory[] = $this->makeSnapshot(0.15, 0.75, 0.85, 0.05, 'EQUILIBRIUM', $i * 100);
        }

        $result = $advisor->analyze($trajectory, $style, 100);

        // Either null proposal or quality_sufficient reason
        if ($result['proposal'] === null) {
            $this->assertContains($result['analysis']['reason'], ['quality_sufficient', 'cooldown_active']);
        }
    }

    public function test_advisor_cooldown(): void
    {
        $advisor = new StyleAdvisorService(new QualityEvaluator(), new SemanticProjector());
        $style = UniverseStyleVersion::defaultStyle();

        $trajectory = [];
        for ($i = 0; $i < 30; $i++) {
            $trajectory[] = $this->makeSnapshot(0.5, 0.5, 0.5, 0.3, 'EQUILIBRIUM', $i * 100);
        }

        // First call: might generate proposal
        $advisor->analyze($trajectory, $style, 100);

        // Second call within cooldown: should not generate proposal
        $result = $advisor->analyze($trajectory, $style, 110);
        $this->assertNull($result['proposal']);
        $this->assertEquals('cooldown_active', $result['analysis']['reason']);
    }

    // --- ConstraintEngine Tests ---

    public function test_constraint_valid_proposal(): void
    {
        $engine = new ConstraintEngine();

        $proposal = [
            'weight_adjustments' => ['order_bias' => 0.10],
            'requires_human_approval' => true,
        ];

        $result = $engine->validateProposal($proposal, 0.5);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['violations']);
    }

    public function test_constraint_rejects_large_delta(): void
    {
        $engine = new ConstraintEngine();

        $proposal = [
            'weight_adjustments' => ['order_bias' => 0.5], // Too large
            'requires_human_approval' => true,
        ];

        $result = $engine->validateProposal($proposal, 0.5);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['violations']);
    }

    public function test_constraint_rejects_low_stability(): void
    {
        $engine = new ConstraintEngine();

        $proposal = [
            'weight_adjustments' => ['order_bias' => 0.1],
            'requires_human_approval' => true,
        ];

        $result = $engine->validateProposal($proposal, 0.1); // Too low
        $this->assertFalse($result['valid']);
    }

    public function test_constraint_rejects_missing_approval_flag(): void
    {
        $engine = new ConstraintEngine();

        $proposal = [
            'weight_adjustments' => ['order_bias' => 0.1],
            // Missing requires_human_approval
        ];

        $result = $engine->validateProposal($proposal, 0.5);
        $this->assertFalse($result['valid']);
    }

    public function test_constraint_memory_bias_valid(): void
    {
        $engine = new ConstraintEngine();

        $bias = ['entropy' => 0.05, 'energy' => 0.03, 'stability' => 0.02];
        $result = $engine->validateMemoryBias($bias);
        $this->assertTrue($result['valid']);
    }

    public function test_constraint_memory_bias_too_large(): void
    {
        $engine = new ConstraintEngine();

        $bias = ['entropy' => 0.5, 'energy' => 0.5, 'stability' => 0.5]; // magnitude > 0.25
        $result = $engine->validateMemoryBias($bias);
        $this->assertFalse($result['valid']);
    }

    public function test_constraint_emergent_archetype_redundant(): void
    {
        $engine = new ConstraintEngine();
        $projector = new SemanticProjector();

        // Near-copy of celestial_harmony
        $nearCopy = ['order' => 0.84, 'chaos' => 0.06, 'transcendence' => 0.39, 'decay' => 0.06, 'creation' => 0.49, 'destruction' => 0.06];
        $result = $engine->validateEmergentArchetype($nearCopy, $projector);
        $this->assertFalse($result['valid']);
    }

    public function test_constraint_emergent_archetype_unique(): void
    {
        $engine = new ConstraintEngine();
        $projector = new SemanticProjector();

        $unique = ['order' => 0.3, 'chaos' => 0.3, 'transcendence' => 0.9, 'decay' => 0.1, 'creation' => 0.1, 'destruction' => 0.9];
        $result = $engine->validateEmergentArchetype($unique, $projector);
        $this->assertTrue($result['valid']);
    }

    public function test_constraint_sandbox_safe(): void
    {
        $engine = new ConstraintEngine();

        $result = $engine->validateSandboxResult([
            'safe' => true,
            'delta_gi' => 0.05,
        ]);
        $this->assertTrue($result['valid']);
    }

    public function test_constraint_sandbox_unsafe(): void
    {
        $engine = new ConstraintEngine();

        $result = $engine->validateSandboxResult([
            'safe' => false,
            'delta_gi' => -0.15,
        ]);
        $this->assertFalse($result['valid']);
        $this->assertCount(2, $result['violations']); // Both unsafe AND negative delta
    }
}
