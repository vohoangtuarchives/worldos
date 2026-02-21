<?php

namespace App\StoryEngine\Simulation\Phases;

use App\StoryEngine\Simulation\SimulationPhaseInterface;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\Balancing\WorldHealthCalculator;
use App\StoryEngine\Seed;

use Tuzy\Application\WorldManagement\Services\AlertService;

class MetricsPhase implements SimulationPhaseInterface
{
    public function __construct(
        protected ?AlertService $alertService = null
    ) {
        // Optional injection to avoid breaking existing tests if they don't provide it
        // Ideally should be mandatory
        if (!$this->alertService) {
            $this->alertService = new AlertService();
        }
    }

    public function execute(SimulationContext $context): void
    {
        $health = WorldHealthCalculator::calculate($context->world);
        
        // Count Forks in last 24h for current world
        // (Mock logic: Assuming context->worldId is parent)
        $forkRate = 0;
        if ($context->worldId) {
             $forkRate = \App\Models\World::where('parent_id', $context->worldId)
                ->where('created_at', '>=', now()->subDay())
                ->count();
        }

        $context->metrics = [
            'chapter' => $context->currentChapter,
            'active_seeds' => count($context->seeds),
            'resolved_seed' => $context->activeSeed ? $context->activeSeed->type : 'none',
            'max_dimension' => $this->getMaxDimension($context->seeds),
            'power_tier' => $context->character->powerTier,
            'public_awareness' => $context->world->publicAwareness,
            'danger_score' => $health->dangerScore(),
            'fork_rate' => $forkRate,
            'fatal_attempts' => 0, // Hook up to EventBus validation errors later
            'faction_stats' => array_map(fn($f) => [
                'id' => $f->id,
                'stress' => $f->economy->stressLevel(),
                'success' => array_sum($f->memory->successCounter),
            ], $context->world->factions),
        ];

        // ADR-0008: Check Operator Health
        if ($context->worldId) {
            // We need a World model to pass to AlertService.
            // Since we only have the ID, we could fetch it, or refactor AlertService to take ID.
            // For now, let's fetch shallow model or just use ID if refactored.
            // AlertService requires a World Model because it updates health_status on it.
            $world = \App\Models\World::find($context->worldId);
            if ($world) {
                $this->alertService->checkHealth($world, $context->metrics);
            }
        }
    }

    private function getMaxDimension(array $seeds): string
    {
         $maxLevel = -1;
         foreach ($seeds as $s) {
             $l = $s->getDimensionLevel();
             if ($l > $maxLevel) $maxLevel = $l;
         }
         return Seed::getDimensionFromLevel($maxLevel);
    }
}
