<?php

namespace Tests\Unit\Domains\Meta;

use Tuzy\Domain\Meta\Policies\ExtinctionPolicy;
use Tuzy\Domain\Meta\Aggregates\MetaLayer;
use Tests\TestCase;

class ExtinctionPolicyTest extends TestCase
{
    public function test_it_does_not_trigger_extinction_when_balanced()
    {
        $policy = new ExtinctionPolicy();
        $metaLayer = new MetaLayer();
        
        // Balanced State
        $metaLayer->chaosPool = 10.0;
        $metaLayer->ideologyVector = ['order' => 0.5, 'chaos' => 0.5];
        $metaLayer->stabilityIndex = 0.5;

        $this->assertFalse($policy->shouldTriggerExtinction($metaLayer));
    }

    public function test_it_triggers_extinction_on_high_chaos()
    {
        $policy = new ExtinctionPolicy();
        $metaLayer = new MetaLayer();

        // Extreme Chaos
        $metaLayer->chaosPool = 200.0; // Threshold is 150

        $this->assertTrue($policy->shouldTriggerExtinction($metaLayer));
        
        // Calculate Severity
        $severity = $policy->calculateSeverity($metaLayer);
        $this->assertGreaterThan(0.2, $severity);
    }

    public function test_it_triggers_extinction_on_ideological_collapse()
    {
        $policy = new ExtinctionPolicy();
        $metaLayer = new MetaLayer();

        // Totalitarian Order
        $metaLayer->chaosPool = 10.0;
        $metaLayer->ideologyVector = ['order' => 0.96, 'chaos' => 0.04]; // Threshold usually > 0.95

        $this->assertTrue($policy->shouldTriggerExtinction($metaLayer));
    }
}
