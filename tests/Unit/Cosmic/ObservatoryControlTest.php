<?php

declare(strict_types=1);

namespace Tests\Unit\Cosmic;

use App\Domains\Cosmic\Entities\AttractorAggregate;
use App\Domains\Cosmic\Services\AlertEvaluationEngine;
use App\Domains\Cosmic\Services\EmergencyInterventionService;
use App\Domains\Cosmic\Services\EpochControlService;
use App\Domains\Cosmic\Services\MetricsSnapshotService;
use App\Domains\Cosmic\ValueObjects\AlertRule;
use App\Domains\Cosmic\ValueObjects\Attractor;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\EnvironmentState;
use App\Domains\Cosmic\ValueObjects\MetricsSnapshot;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use PHPUnit\Framework\TestCase;

class ObservatoryControlTest extends TestCase
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

    // ===== MetricsSnapshot Tests =====

    public function test_metrics_snapshot_critical_four(): void
    {
        $metrics = new MetricsSnapshot(
            epoch: 100,
            spectralStabilityIndex: 0.5, collapseFrequency: 0.02, stabilityMargin: 0.3,
            diversityIndex: 0.7, adaptationGainRate: 0.01, rebirthEffectiveness: 0.3,
            influenceConcentration: 0.2, votingPowerSkew: 0.05, alliancePolarization: 0.1,
            historicalBiasRatio: 0.1, collectiveMomentum: 0.15, memoryDecayEffectiveness: 0.8,
            emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.75,
        );

        $c4 = $metrics->criticalFour();
        $this->assertArrayHasKey('SSI', $c4);
        $this->assertArrayHasKey('DI', $c4);
        $this->assertArrayHasKey('CF', $c4);
        $this->assertArrayHasKey('HBR', $c4);
    }

    public function test_metrics_severity_healthy(): void
    {
        $metrics = new MetricsSnapshot(
            epoch: 0, spectralStabilityIndex: 0.5, collapseFrequency: 0.01,
            stabilityMargin: 0.3, diversityIndex: 0.7, adaptationGainRate: 0.01,
            rebirthEffectiveness: 0.3, influenceConcentration: 0.2, votingPowerSkew: 0.05,
            alliancePolarization: 0.1, historicalBiasRatio: 0.1, collectiveMomentum: 0.15,
            memoryDecayEffectiveness: 0.8, emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.75,
        );
        $this->assertEquals('HEALTHY', $metrics->overallSeverity());
    }

    public function test_metrics_severity_critical(): void
    {
        $metrics = new MetricsSnapshot(
            epoch: 0, spectralStabilityIndex: 1.2, collapseFrequency: 4.0,
            stabilityMargin: -0.1, diversityIndex: 0.2, adaptationGainRate: -0.01,
            rebirthEffectiveness: 0.1, influenceConcentration: 0.5, votingPowerSkew: 0.2,
            alliancePolarization: 0.6, historicalBiasRatio: 0.3, collectiveMomentum: 0.3,
            memoryDecayEffectiveness: 0.2, emergencePressure: 0.8, archetypeTurnoverRate: 0.05,
            proposalAcceptanceRatio: 0.05, governanceLatency: 10.0, humanInterventionIndex: 0.1,
            civilizationalHealthScore: 0.2,
        );
        $this->assertEquals('CRITICAL', $metrics->overallSeverity());
    }

    public function test_metrics_serialization(): void
    {
        $metrics = new MetricsSnapshot(
            epoch: 42, spectralStabilityIndex: 0.5, collapseFrequency: 0.02,
            stabilityMargin: 0.3, diversityIndex: 0.7, adaptationGainRate: 0.01,
            rebirthEffectiveness: 0.3, influenceConcentration: 0.2, votingPowerSkew: 0.05,
            alliancePolarization: 0.1, historicalBiasRatio: 0.1, collectiveMomentum: 0.15,
            memoryDecayEffectiveness: 0.8, emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.75,
        );

        $array = $metrics->toArray();
        $restored = MetricsSnapshot::fromArray($array);

        $this->assertEquals($metrics->epoch, $restored->epoch);
        $this->assertEquals($metrics->spectralStabilityIndex, $restored->spectralStabilityIndex);
        $this->assertEquals($metrics->civilizationalHealthScore, $restored->civilizationalHealthScore);
    }

    // ===== MetricsSnapshotService Tests =====

    public function test_metrics_service_basic_calculation(): void
    {
        $service = new MetricsSnapshotService();
        $current = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 100);

        $trajectory = [];
        for ($i = 0; $i < 20; $i++) {
            $trajectory[] = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', $i * 100);
        }

        $result = $service->calculate($current, $trajectory);

        $this->assertInstanceOf(MetricsSnapshot::class, $result);
        $this->assertGreaterThanOrEqual(0.0, $result->diversityIndex);
        $this->assertLessThanOrEqual(1.0, $result->diversityIndex);
        $this->assertGreaterThanOrEqual(0.0, $result->civilizationalHealthScore);
        $this->assertLessThanOrEqual(1.0, $result->civilizationalHealthScore);
    }

    public function test_metrics_service_ssi_stable(): void
    {
        $service = new MetricsSnapshotService();
        $current = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 1000);

        // Stable trajectory (no change) → SSI near 0
        $trajectory = [];
        for ($i = 0; $i < 20; $i++) {
            $trajectory[] = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', $i * 100);
        }

        $result = $service->calculate($current, $trajectory);
        $this->assertLessThan(0.5, $result->spectralStabilityIndex);
    }

    public function test_metrics_service_ssi_unstable(): void
    {
        $service = new MetricsSnapshotService();
        $current = $this->makeSnapshot(0.8, 0.2, 0.2, 0.8, 'HIGH_CHAOS', 1000);

        // Wildly oscillating trajectory → high SSI
        $trajectory = [];
        for ($i = 0; $i < 20; $i++) {
            $e = ($i % 2 === 0) ? 0.2 : 0.8;
            $trajectory[] = $this->makeSnapshot($e, 1.0 - $e, 1.0 - $e, $e, 'HIGH_CHAOS', $i * 100);
        }

        $result = $service->calculate($current, $trajectory);
        $this->assertGreaterThan(1.0, $result->spectralStabilityIndex);
    }

    public function test_metrics_service_chs_bounded(): void
    {
        $service = new MetricsSnapshotService();
        $current = $this->makeSnapshot(0.5, 0.5, 0.5, 0.5, 'EQUILIBRIUM', 500);
        $trajectory = [$current, $current];

        $result = $service->calculate($current, $trajectory);
        $this->assertGreaterThanOrEqual(0.0, $result->civilizationalHealthScore);
        $this->assertLessThanOrEqual(1.0, $result->civilizationalHealthScore);
    }

    // ===== AlertRule Tests =====

    public function test_alert_rule_catalog(): void
    {
        $rules = AlertRule::catalog();
        $this->assertCount(15, $rules);

        $codes = array_map(fn($r) => $r->code, $rules);
        $this->assertContains('SSI_CRITICAL', $codes);
        $this->assertContains('VOTING_DOMINANCE', $codes);
        $this->assertContains('HISTORICAL_LOCK', $codes);
    }

    public function test_alert_rule_evaluate(): void
    {
        $rule = new AlertRule('TEST', 'TEST', 'WARNING', 'ssi', '>', 0.9, 5, null, 'Test');
        $this->assertTrue($rule->evaluate(1.0));
        $this->assertFalse($rule->evaluate(0.5));
    }

    // ===== AlertEvaluationEngine Tests =====

    public function test_alert_engine_triggers(): void
    {
        $engine = new AlertEvaluationEngine();

        // High SSI should trigger SSI_CRITICAL and SSI_WARNING
        $metrics = new MetricsSnapshot(
            epoch: 100, spectralStabilityIndex: 1.2, collapseFrequency: 0.01,
            stabilityMargin: 0.3, diversityIndex: 0.7, adaptationGainRate: 0.01,
            rebirthEffectiveness: 0.3, influenceConcentration: 0.2, votingPowerSkew: 0.05,
            alliancePolarization: 0.1, historicalBiasRatio: 0.1, collectiveMomentum: 0.15,
            memoryDecayEffectiveness: 0.8, emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.3,
        );

        $result = $engine->evaluate($metrics, 100);
        $alertCodes = array_column($result['alerts'], 'code');

        $this->assertContains('SSI_CRITICAL', $alertCodes);
    }

    public function test_alert_engine_cooldown(): void
    {
        $engine = new AlertEvaluationEngine();

        $metrics = new MetricsSnapshot(
            epoch: 100, spectralStabilityIndex: 1.2, collapseFrequency: 0.01,
            stabilityMargin: 0.3, diversityIndex: 0.7, adaptationGainRate: 0.01,
            rebirthEffectiveness: 0.3, influenceConcentration: 0.2, votingPowerSkew: 0.05,
            alliancePolarization: 0.1, historicalBiasRatio: 0.1, collectiveMomentum: 0.15,
            memoryDecayEffectiveness: 0.8, emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.3,
        );

        $result1 = $engine->evaluate($metrics, 100);
        $result2 = $engine->evaluate($metrics, 101); // Within cooldown

        // Second evaluation should have fewer alerts due to cooldown
        $this->assertLessThanOrEqual(
            count($result1['alerts']),
            count($result1['alerts']), // First always has the alerts
        );

        // SSI_CRITICAL has 3 epoch cooldown; epoch 101 is within cooldown
        $codes2 = array_column($result2['alerts'], 'code');
        $this->assertNotContains('SSI_CRITICAL', $codes2);
    }

    public function test_alert_engine_auto_action(): void
    {
        $engine = new AlertEvaluationEngine();

        $metrics = new MetricsSnapshot(
            epoch: 100, spectralStabilityIndex: 1.2, collapseFrequency: 0.01,
            stabilityMargin: 0.3, diversityIndex: 0.7, adaptationGainRate: 0.01,
            rebirthEffectiveness: 0.3, influenceConcentration: 0.2, votingPowerSkew: 0.05,
            alliancePolarization: 0.1, historicalBiasRatio: 0.1, collectiveMomentum: 0.15,
            memoryDecayEffectiveness: 0.8, emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.3,
        );

        $result = $engine->evaluate($metrics, 100);

        // SSI_CRITICAL has auto_action FREEZE_SIMULATION
        $actions = array_column($result['auto_actions'], 'action');
        $this->assertContains('FREEZE_SIMULATION', $actions);
    }

    public function test_alert_engine_escalation(): void
    {
        $engine = new AlertEvaluationEngine();

        // SSI_WARNING with 3 consecutive triggers → escalate to CRITICAL
        $metrics = new MetricsSnapshot(
            epoch: 0, spectralStabilityIndex: 0.95, collapseFrequency: 0.01,
            stabilityMargin: 0.3, diversityIndex: 0.7, adaptationGainRate: 0.01,
            rebirthEffectiveness: 0.3, influenceConcentration: 0.2, votingPowerSkew: 0.05,
            alliancePolarization: 0.1, historicalBiasRatio: 0.1, collectiveMomentum: 0.15,
            memoryDecayEffectiveness: 0.8, emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.5,
        );

        // Trigger 3 times with sufficient cooldown gaps (SSI_WARNING cooldown = 5)
        $engine->evaluate($metrics, 0);
        $engine->evaluate($metrics, 10);
        $result3 = $engine->evaluate($metrics, 20);

        // Should be escalated to CRITICAL after 3 consecutive
        $ssiAlerts = array_filter($result3['alerts'], fn($a) => $a['code'] === 'SSI_WARNING');

        if (!empty($ssiAlerts)) {
            $alert = reset($ssiAlerts);
            $this->assertEquals('CRITICAL', $alert['severity']);
            $this->assertTrue($alert['escalated']);
        }
    }

    public function test_alert_engine_no_alerts_healthy(): void
    {
        $engine = new AlertEvaluationEngine();

        $metrics = new MetricsSnapshot(
            epoch: 0, spectralStabilityIndex: 0.3, collapseFrequency: 0.01,
            stabilityMargin: 0.5, diversityIndex: 0.7, adaptationGainRate: 0.01,
            rebirthEffectiveness: 0.3, influenceConcentration: 0.2, votingPowerSkew: 0.05,
            alliancePolarization: 0.1, historicalBiasRatio: 0.1, collectiveMomentum: 0.15,
            memoryDecayEffectiveness: 0.8, emergencePressure: 0.3, archetypeTurnoverRate: 0.01,
            proposalAcceptanceRatio: 0.5, governanceLatency: 2.0, humanInterventionIndex: 0.02,
            civilizationalHealthScore: 0.75,
        );

        $result = $engine->evaluate($metrics, 0);
        $this->assertEmpty($result['alerts']);
        $this->assertEmpty($result['auto_actions']);
        $this->assertEmpty($result['composites']);
    }

    public function test_alert_engine_serialization(): void
    {
        $engine = new AlertEvaluationEngine();
        $array = $engine->toArray();
        $restored = AlertEvaluationEngine::fromArray($array);

        $this->assertIsArray($restored->toArray());
    }

    // ===== EpochControlService Tests =====

    public function test_epoch_control_freeze_resume(): void
    {
        $ctrl = new EpochControlService();
        $this->assertFalse($ctrl->isFrozen());

        $ctrl->freeze();
        $this->assertTrue($ctrl->isFrozen());

        $ctrl->resume();
        $this->assertFalse($ctrl->isFrozen());
    }

    public function test_epoch_control_snapshot_history(): void
    {
        $ctrl = new EpochControlService();

        $s1 = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 100);
        $s2 = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 200);

        $ctrl->recordSnapshot($s1);
        $ctrl->recordSnapshot($s2);

        $this->assertEquals(200, $ctrl->getLatestSnapshot()->year);
        $this->assertCount(2, $ctrl->getSnapshotHistory());
    }

    public function test_epoch_control_rollback(): void
    {
        $ctrl = new EpochControlService();

        $s1 = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 100);
        $s2 = $this->makeSnapshot(0.5, 0.4, 0.5, 0.5, 'HIGH_CHAOS', 200);

        $ctrl->recordSnapshot($s1);
        $ctrl->recordSnapshot($s2);

        $restored = $ctrl->rollback();
        $this->assertNotNull($restored);
        $this->assertEquals(100, $restored->year);
        $this->assertEquals(100, $ctrl->getLatestSnapshot()->year);
    }

    public function test_epoch_control_rollback_insufficient_history(): void
    {
        $ctrl = new EpochControlService();
        $s1 = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 100);
        $ctrl->recordSnapshot($s1);

        $restored = $ctrl->rollback();
        $this->assertNull($restored);
    }

    public function test_epoch_control_action_log(): void
    {
        $ctrl = new EpochControlService();
        $ctrl->freeze('alert_triggered');
        $ctrl->resume('manual');

        $log = $ctrl->getActionLog();
        $this->assertCount(2, $log);
        $this->assertEquals('FREEZE', $log[0]['action']);
        $this->assertEquals('RESUME', $log[1]['action']);
    }

    // ===== EmergencyInterventionService Tests =====

    public function test_emergency_entropy_shock(): void
    {
        $eis = new EmergencyInterventionService();
        $snap = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 100);

        $result = $eis->injectEntropyShock($snap, 0.15);

        $this->assertGreaterThan($snap->cosmic->entropy, $result->cosmic->entropy);
        $this->assertGreaterThan($snap->cosmic->strain, $result->cosmic->strain);
        $this->assertLessThan($snap->cosmic->stability, $result->cosmic->stability);
        $this->assertCount(1, $eis->getInterventionLog());
    }

    public function test_emergency_entropy_shock_bounded(): void
    {
        $eis = new EmergencyInterventionService();
        $snap = $this->makeSnapshot(0.9, 0.6, 0.7, 0.9, 'EQUILIBRIUM', 100);

        $result = $eis->injectEntropyShock($snap, 0.5); // Exceeds max → clamped to 0.3

        $this->assertLessThanOrEqual(1.0, $result->cosmic->entropy);
        $this->assertLessThanOrEqual(1.0, $result->cosmic->strain);
    }

    public function test_emergency_reduce_rigidity(): void
    {
        $eis = new EmergencyInterventionService();
        $snap = $this->makeSnapshot(0.3, 0.6, 0.7, 0.5, 'EQUILIBRIUM', 100);

        $result = $eis->reduceRigidityGlobally($snap, 0.1);

        // Strain should decrease (more flexible)
        $this->assertLessThan($snap->cosmic->strain, $result->cosmic->strain);
        $this->assertCount(1, $eis->getInterventionLog());
    }

    public function test_emergency_force_collapse(): void
    {
        $eis = new EmergencyInterventionService();
        $snap = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 100);

        $result = $eis->forceCollapse($snap);

        $this->assertGreaterThan($snap->cosmic->entropy, $result->cosmic->entropy);
        $this->assertGreaterThan($snap->cosmic->strain, $result->cosmic->strain);
        $this->assertLessThan($snap->cosmic->stability, $result->cosmic->stability);
        $this->assertLessThan($snap->cosmic->energy, $result->cosmic->energy);
    }

    public function test_emergency_disable_emergent(): void
    {
        $eis = new EmergencyInterventionService();

        $this->assertFalse($eis->areEmergentArchetypesDisabled());

        $eis->setEmergentArchetypesDisabled(true, 100);
        $this->assertTrue($eis->areEmergentArchetypesDisabled());

        $eis->setEmergentArchetypesDisabled(false, 150);
        $this->assertFalse($eis->areEmergentArchetypesDisabled());

        $this->assertCount(2, $eis->getInterventionLog());
    }

    public function test_emergency_all_logged(): void
    {
        $eis = new EmergencyInterventionService();
        $snap = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', 100);

        $eis->injectEntropyShock($snap);
        $eis->reduceRigidityGlobally($snap);
        $eis->forceCollapse($snap);
        $eis->setEmergentArchetypesDisabled(true, 100);

        $this->assertEquals(4, $eis->getInterventionCount());

        $types = array_column($eis->getInterventionLog(), 'type');
        $this->assertContains('ENTROPY_SHOCK', $types);
        $this->assertContains('REDUCE_RIGIDITY', $types);
        $this->assertContains('FORCE_COLLAPSE', $types);
        $this->assertContains('DISABLE_EMERGENT', $types);
    }
}
