<?php

namespace App\StoryEngine\Material;

use WorldOS\Legacy\Domain\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class MaterialEventGenerator
{
    private MaterialNarrativeMapper $mapper;
    private MaterialRepositoryInterface $repository;

    public function __construct(
        MaterialNarrativeMapper $mapper,
        MaterialRepositoryInterface $repository
    ) {
        $this->mapper = $mapper;
        $this->repository = $repository;
    }

    /**
     * Generate story events from material states in a world.
     * 
     * @return array Events with narrative text and metadata
     */
    public function generateEvents(World $world): array
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $events = [];

        // Generate activation events for newly activated materials
        foreach ($instances as $instance) {
            if ($instance->activation_epoch === $world->tick) {
                $events[] = [
                    'type' => 'material_activation',
                    'material_code' => $instance->material->code,
                    'narrative' => $this->mapper->generateActivationEvent($instance),
                    'epoch' => $world->tick
                ];
            }
        }

        // Generate mutation events
        foreach ($instances as $instance) {
            $mutationState = $instance->mutation_state ?? [];
            if (isset($mutationState['mutation_epoch']) && $mutationState['mutation_epoch'] === $world->tick) {
                $events[] = [
                    'type' => 'material_mutation',
                    'from' => $mutationState['mutated_from'] ?? 'unknown',
                    'to' => $instance->material->code,
                    'narrative' => $this->mapper->generateMutationEvent(
                        $mutationState['mutated_from'] ?? 'unknown',
                        $instance->material->code
                    ),
                    'epoch' => $world->tick
                ];
            }
        }

        // Detect and generate conflict events
        $activeInstances = $instances->filter(fn($i) => $i->activation_epoch !== null && !$i->retired_at);
        $conflicts = $this->mapper->detectConflicts($activeInstances->all());

        foreach ($conflicts as $conflict) {
            $events[] = [
                'type' => 'material_conflict',
                'materials' => [$conflict['material1'], $conflict['material2']],
                'narrative' => $conflict['narrative'],
                'epoch' => $world->tick
            ];
        }

        return $events;
    }

    /**
     * Generate a narrative summary of the current world state.
     */
    public function generateWorldNarrative(World $world): string
    {
        $instances = $this->repository->getInstancesForWorld($world->id);
        $activeInstances = $instances->filter(fn($i) => $i->activation_epoch !== null && !$i->retired_at);

        if ($activeInstances->isEmpty()) {
            return "The world is in a state of dormancy, awaiting the forces that will shape its destiny.";
        }

        $materialNames = $activeInstances->map(fn($i) => strtolower(str_replace('_', ' ', $i->material->code)))->toArray();
        $count = count($materialNames);

        if ($count === 1) {
            return "The world is dominated by {$materialNames[0]}.";
        } elseif ($count === 2) {
            return "The world is shaped by {$materialNames[0]} and {$materialNames[1]}.";
        } else {
            $last = array_pop($materialNames);
            $list = implode(', ', $materialNames);
            return "The world is shaped by {$list}, and {$last}.";
        }
    }
}
