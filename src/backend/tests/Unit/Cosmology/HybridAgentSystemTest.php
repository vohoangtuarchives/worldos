<?php

namespace Tests\Unit\Cosmology;

use App\Domains\Cosmology\Agents\LegendaryAgent;
use App\Domains\Cosmology\Agents\PopulationBlock;
use App\Domains\Cosmology\Entities\WorldStateVector;
use PHPUnit\Framework\TestCase;

class HybridAgentSystemTest extends TestCase
{
    public function test_legendary_agent_defies_fate_with_cost()
    {
        $agent = new LegendaryAgent('Sargon', LegendaryAgent::ARCHETYPE_WARLORD);
        
        // Initial state
        $vector = WorldStateVector::create(
            order: 0.5,
            entropy: 0.5,
            cohesion: 0.5,
            legitimacy: 0.5,
            innovation: 0.5,
            military: 0.5,
            inequality: 0.5,
            trauma: 0.1,
            eliteCohesion: 0.5,
            resourceStock: 0.5
        );

        // Defy Fate: Restore Order
        $next = $agent->defyFate($vector, 'RESTORE_ORDER');

        // Verify Impact
        $this->assertGreaterThan(0.5, $next->getOrder()); // Order increased
        $this->assertLessThan(0.5, $next->getEntropy()); // Entropy decreased

        // Verify Cost (Cosmic Scar)
        $this->assertGreaterThan(0.1, $next->getTrauma()); // Trauma increased by 0.1

        // Verify Agent Cost
        $this->assertLessThan(1.0, $agent->willpower); // Willpower burned
        $this->assertGreaterThan(0.0, $agent->destinyDebt); // Debt accumulated
    }

    public function test_population_block_radicalizes_due_to_inequality()
    {
        $pop = new PopulationBlock();

        // High Inequality, High Trauma
        $vector = WorldStateVector::create(
            order: 0.5,
            entropy: 0.5,
            cohesion: 0.5,
            legitimacy: 0.2, // Low Legitimacy
            innovation: 0.5,
            military: 0.5,
            inequality: 0.9, // Extreme Inequality
            trauma: 0.5, // High Trauma
            eliteCohesion: 0.5,
            resourceStock: 0.5
        );

        $pressure = $pop->generatePressure($vector);

        // Should generate negative pressure on Order and Legitimacy
        $this->assertLessThan(0.0, $pressure->get(WorldStateVector::DIMENSION_ORDER));
        $this->assertLessThan(0.0, $pressure->get(WorldStateVector::DIMENSION_LEGITIMACY));

        // Should fuel Entropy
        $this->assertGreaterThan(0.0, $pressure->get(WorldStateVector::DIMENSION_ENTROPY));
    }
}
