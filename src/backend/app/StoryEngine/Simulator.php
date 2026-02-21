<?php

namespace App\StoryEngine;

class Simulator
{
    /** @var Seed[] */
    public array $seeds = [];
    public WorldState $world;
    public CharacterState $character;
    
    // Phase Y: Persistence
    public ?\App\StoryEngine\Persistence\EventStore $eventStore = null;
    public string $timelineId = 'simulation_test';

    public function __construct(string $timelineId = 'simulation_test')
    {
        $this->timelineId = $timelineId;
        $this->eventStore = new \App\StoryEngine\Persistence\EventStore();
        
        $this->world = new WorldState();
        $this->character = new CharacterState();

        // Initialize Factions (Phase V)
        // ADR: Load from DB if available, otherwise fallback/empty
        if (isset($this->world->id)) {
            $dbFactions = \App\Models\Faction::where('world_id', $this->world->id)->get();
            foreach ($dbFactions as $f) {
                $state = new FactionState((string)$f->id, $f->name, $f->type);
                // Hydrate attributes if any
                if ($f->attributes) {
                     $attrs = $f->attributes;
                     if (isset($attrs['cohesion'])) $state->cohesion = $attrs['cohesion'];
                     // Economy might need hydration too, but Econ is transient mostly?
                     // If we want Econ persistence, we'd need to save it. 
                     // For now, new Economy(rand) is fine as per original logic, or specific hydration
                }
                $this->world->factions[] = $state;
            }
        }
        
        // Fallback for Tests/Legacy if no DB factions found and no world ID? 
        // Actually, existing tests might rely on hardcoded factions.
        // Let's keep hardcoded as default if DB returns empty AND we are in test mode?
        // Or safer: Create a Seeder for tests.
        if (empty($this->world->factions)) {
            $this->world->factions = [
                new FactionState('sect_1', 'Azure Cloud Sect', 'Sect'),
                new FactionState('clan_1', 'Iron Blood Clan', 'Clan'),
                new FactionState('guild_1', 'Golden Pavilion', 'Guild'),
            ];
        }

        // Initial Seed
        $this->seeds[] = new Seed(SeedTransition::TYPE_POWER_GAP, 'personal', 5);
    }

    public function run(int $chapters): array
    {
        $metrics = [];
        $validator = new \Tuzy\Application\World\Services\WorldLawValidator();

        // Build Pipeline
        $pipeline = new \App\StoryEngine\Simulation\SimulationPipeline();
        $pipeline
            ->addPhase(new \App\StoryEngine\Simulation\Phases\PhysicsPhase())
            ->addPhase(new \App\StoryEngine\Simulation\Phases\SeedSelectionPhase())
            ->addPhase(new \App\StoryEngine\Simulation\Phases\UnifiedRulePhase($validator))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\FactionActionPhase($validator, $this->eventStore))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\EconomicPhase($validator))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\BalancingPhase($validator))
            ->addPhase(new \App\StoryEngine\Simulation\Phases\MetricsPhase());

        for ($i = 1; $i <= $chapters; $i++) {
            // ADR-0008: Kill Switch & Safe Mode Check
            $safeMode = false;
            $worldId = $this->world->id ?? null; // Use class property

            if ($worldId) {
                // Fetch fresh status
                $freshWorld = \App\Models\World::find($worldId);
                if ($freshWorld) {
                    if ($freshWorld->health_status === \Tuzy\Domain\World\ValueObject\WorldHealthStatus::HALTED) {
                         $metrics[] = ['status' => 'HALTED', 'message' => 'Simulation stopped by Kill Switch'];
                         break;
                    }
                    if ($freshWorld->status === 'SAFE_MODE') { 
                        $safeMode = true;
                    }
                }
            }

            // Create Context for this Step
            $context = new \App\StoryEngine\Simulation\SimulationContext(
                $this->world,
                $this->character,
                $this->seeds,
                $this->timelineId,
                $i,
                $worldId,
                $safeMode
            );

            // Execute Pipeline
            $pipeline->run($context);

            // Sync State Back from Context
            // (Objects are by reference, but Arrays need explicit sync if replaced)
            $this->seeds = $context->seeds;
            
            $metrics[] = $context->metrics;
        }

        return $metrics;
    }
}
