<?php

/**
 * Material System Usage Examples
 * 
 * This file demonstrates how to use the Material Mutation and StoryEngine systems.
 */

namespace App\Examples;

use WorldOS\Legacy\Application\Material\Mutation\MutationTriggerDetector;
use WorldOS\Legacy\Application\Material\Mutation\MutationExecutor;
use App\StoryEngine\Material\MaterialEventGenerator;
use App\Models\World;

class MaterialSystemExample
{
    /**
     * Example 1: Detect and execute mutations for a world
     */
    public function detectAndExecuteMutations(World $world)
    {
        $detector = app(MutationTriggerDetector::class);
        $executor = app(MutationExecutor::class);

        // Detect materials ready to mutate
        $mutations = $detector->detectMutations($world);

        echo "Detected {count($mutations)} materials ready to mutate:\n";
        foreach ($mutations as $instanceId => $pathway) {
            echo "- Instance {$instanceId}: {$pathway['description']}\n";
        }

        // Execute mutations
        $mutatedInstances = $executor->executeMutations($mutations);

        echo "\nExecuted {count($mutatedInstances)} mutations:\n";
        foreach ($mutatedInstances as $instance) {
            $from = $instance->mutation_state['mutated_from'] ?? 'unknown';
            $to = $instance->material->code;
            echo "- {$from} → {$to}\n";
        }

        return $mutatedInstances;
    }

    /**
     * Example 2: Generate story events from material states
     */
    public function generateStoryEvents(World $world)
    {
        $generator = app(MaterialEventGenerator::class);

        // Generate events for current tick
        $events = $generator->generateEvents($world);

        echo "Generated {count($events)} story events:\n";
        foreach ($events as $event) {
            echo "\n[{$event['type']}] Epoch {$event['epoch']}:\n";
            echo "  {$event['narrative']}\n";
        }

        return $events;
    }

    /**
     * Example 3: Get world narrative summary
     */
    public function getWorldNarrative(World $world)
    {
        $generator = app(MaterialEventGenerator::class);

        $narrative = $generator->generateWorldNarrative($world);

        echo "World Narrative:\n";
        echo "  {$narrative}\n";

        return $narrative;
    }

    /**
     * Example 4: Complete simulation tick with materials
     */
    public function simulateTickWithMaterials(World $world)
    {
        $bridge = app(\WorldOS\Legacy\Domain\Material\MaterialWorldBridge::class);
        $generator = app(MaterialEventGenerator::class);

        // Build world context
        $worldContext = [
            'sacred' => 0.85,
            'authority' => 0.7,
            'violence' => 0.3,
            // ... other archetype weights
        ];

        // Process material tick
        $effects = $bridge->processTick($world, $worldContext);

        echo "Material Effects:\n";
        echo "- Cohesion: {$effects['cohesion_modifier']}\n";
        echo "- Entropy: {$effects['entropy_modifier']}\n";
        echo "- Collapsed: " . implode(', ', $effects['collapsed_materials']) . "\n";

        if (isset($effects['mutations'])) {
            echo "- Mutations: {$effects['mutations']['count']}\n";
            foreach ($effects['mutations']['instances'] as $mutation) {
                echo "  * {$mutation['from']} → {$mutation['to']}\n";
            }
        }

        // Generate story events
        $events = $generator->generateEvents($world);

        echo "\nStory Events:\n";
        foreach ($events as $event) {
            echo "- {$event['narrative']}\n";
        }

        return [
            'effects' => $effects,
            'events' => $events
        ];
    }
}
