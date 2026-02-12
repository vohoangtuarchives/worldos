<?php

namespace App\Domains\Saga;

use App\Domains\CognitiveKernel\ArchetypePool;
use App\Domains\Material\MaterialSeeder;
use App\Domains\Material\MaterialWorldBridge;
use App\Domains\Material\MaterialArchetypeCoupler;
use App\Models\World;

/**
 * Saga Runner
 * 
 * Meta-level orchestrator for running multiple worlds in sequence.
 * 
 * Key Responsibilities:
 * 1. Create worlds with archetype bias
 * 2. Extract legacy from completed worlds
 * 3. Transfer legacy to next world
 * 4. Observe patterns across saga
 * 
 * Constitutional Constraint:
 * Saga Runner sits ABOVE World Runtime, never modifies worlds directly
 */
class SagaRunner
{
    private ArchetypePool $archetypePool;
    private MythLegacyExtractor $legacyExtractor;
    private SagaObserver $observer;
    private MaterialSeeder $materialSeeder;
    private MaterialWorldBridge $materialBridge;
    private MaterialArchetypeCoupler $materialCoupler;
    private \App\Domains\World\Services\WorldEventLedger $ledger;
    private \App\Domains\World\Services\StageTransitionEngine $transitionEngine;

    public function __construct(
        MaterialSeeder $materialSeeder = null,
        MaterialWorldBridge $materialBridge = null,
        MaterialArchetypeCoupler $materialCoupler = null
    ) {
        $this->archetypePool = new ArchetypePool();
        $this->legacyExtractor = new MythLegacyExtractor();
        $this->observer = new SagaObserver();
        $this->materialSeeder = $materialSeeder ?? app(MaterialSeeder::class);
        $this->materialBridge = $materialBridge ?? app(MaterialWorldBridge::class);
        $this->materialCoupler = $materialCoupler ?? app(MaterialArchetypeCoupler::class);
        $this->ledger = new \App\Domains\World\Services\WorldEventLedger();
        $this->transitionEngine = new \App\Domains\World\Services\StageTransitionEngine(
            $this->ledger,
            app(\App\Domains\Power\PowerStageRegistry::class)
        );
    }

    /**
     * Run the saga synchronously (for web/prototype)
     */
    public function runSync(Saga $saga): void
    {
        // Only reset if starting fresh
        if ($saga->status === Saga::STATUS_PENDING) {
            $saga->started_at = now();
            $saga->current_world_index = 0;
        }

        $saga->status = Saga::STATUS_RUNNING;
        $saga->save();

        // Run until complete
        while (!$saga->isComplete() && !$saga->isFailed()) {
            
            // Validate sequence integrity
            if ($saga->current_world_index >= $saga->world_count) {
                // Should be complete
                $this->completeSaga($saga);
                break;
            }

            // Get or create current world
            $sagaWorld = $saga->getCurrentWorld();
            
            if (!$sagaWorld) {
                // If missing (e.g. fresh start or gap), create it
                // Logic usually handled by createNextWorld but needed here for initialization
                
                // If index > 0, we might need legacy from index-1
                if ($saga->current_world_index > 0) {
                     // Determine if we are creating next or recovering current
                     $sagaWorld = $this->createNextWorld($saga);
                } else {
                     // First world
                     $sagaWorld = $this->createNextWorld($saga);
                }

                if (!$sagaWorld) break; 
            } else {
                // Resume existing world
                // If completed/collapsed, move to next
                if ($sagaWorld->status === SagaWorld::STATUS_COMPLETED || $sagaWorld->status === SagaWorld::STATUS_COLLAPSED) {
                    $saga->current_world_index++;
                    $saga->save();
                    continue; 
                }
            }

            // Simulate this world
            $this->simulateWorld($sagaWorld);
        }
    }

    /**
     * Start a saga (Legacy/Async hook)
     */
    public function start(Saga $saga): void
    {
        $this->runSync($saga);
    }

    /**
     * Simulate a single world's timeline
     */
    private function simulateWorld(SagaWorld $sagaWorld): void
    {
        $world = $sagaWorld->world;
        $ticks = 100; // Increased granularity
        $chronicleInterval = 5; // More frequent writes

        $narrativeAssembler = app(DeepNarrativeAssembler::class);
        $agentOrchestrator = app(\App\Domains\Faction\Services\FactionAgent::class);
        $conflictResolver = app(\App\Domains\Faction\Services\ConflictResolver::class);

        // Ensure factions are initialized with AI data if needed
        $this->ensureFactionsInitialized($world);

        for ($i = 0; $i < $ticks; $i++) {
            $world->tick++;
            $epoch = $world->tick;
            
            // 1. Agent Decision Phase
            $factions = $world->factions;
            $intents = [];
            foreach ($factions as $faction) {
                $agentOrchestrator->executeTurn($faction, $world, $epoch);
                $intents[$faction->id] = \App\Domains\Faction\Enums\FactionIntentType::from($faction->attributes['current_intent']);
            }

            // 2. Conflict Resolution Phase
            $conflictResolver->resolve($world, $intents);

            // Initialize Author Persona for this world once
            $authorRegistry = new \App\Domains\Saga\Author\AuthorRegistry();
            $authorPersonaKey = $world->config['author_persona'] ?? 'WuxiaMaster'; 
            $persona = $authorRegistry->get($authorPersonaKey);
            if ($persona) {
                $narrativeAssembler->setPersona($persona);
            }

            // 3. World Simulation Phase
            // Apply Random Drift
            $this->applyRandomDrift($world);

            // Process Material Effects
            $this->processMaterialEffects($world);

            // 4. Outcome Recording Phase
            foreach ($factions as $faction) {
                $faction->refresh(); // Sync with state updated in ConflictResolver
                $reward = $faction->attributes['tick_reward'] ?? 0.0;
                $reasoning = $faction->attributes['tick_reason'] ?? [];
                $agentOrchestrator->recordOutcome($faction, $epoch, $reward, $reasoning);
            }

            // 5. Evolution Phase (Ledger & Stage)
            $this->transitionEngine->evaluateTransition($world);

            // Check for potential collapse (random chance for chaos)
            if ($this->checkCollapse($world)) {
                // Record Collapse in Ledger
                $reason = $this->getCollapseReason($world);
                $this->ledger->record($world, 'world_collapse', $reason, 1.0, 1.0);

                // Write final chronicle before collapse
                $this->writeChronicle($world, $epoch, $narrativeAssembler, true);
                $this->onWorldComplete($sagaWorld, true);
                return;
            }

            // Write chronicle at intervals
            if ($epoch % $chronicleInterval === 0) {
                $this->writeChronicle($world, $epoch, $narrativeAssembler, false);
            }
        }

        $world->save();
        $this->onWorldComplete($sagaWorld, false);
    }

    /**
     * Ensure factions have initial AI state (Leader, Ideology, etc.)
     */
    private function ensureFactionsInitialized(World $world): void
    {
        foreach ($world->factions as $faction) {
            if (!$faction->leader_data) {
                $faction->updateLeader(\App\Domains\Faction\ValueObjects\Leader::create());
            }
            if (!$faction->ideology_vector) {
                $faction->updateIdeology(\App\Domains\Faction\ValueObjects\IdeologyVector::random());
            }
            if (!$faction->personality_vector) {
                $faction->updatePersonality(\App\Domains\Faction\ValueObjects\PersonalityVector::random());
            }
            if (!$faction->memory_state) {
                $faction->updateMemory(\App\Domains\Faction\ValueObjects\FactionMemory::fresh());
            }
            $faction->save();
        }
    }

    /**
     * Write a chronicle entry for the current epoch
     */
    private function writeChronicle(World $world, int $epoch, DeepNarrativeAssembler $assembler, bool $isCollapse): void
    {
        // Build simple events from current world state
        $events = $this->detectSimpleEvents($world, $epoch, $isCollapse);
        
        // Merge with explicit Ledger events (Epic History)
        $ledgerEvents = $this->detectLedgerEvents($world, $epoch);
        $events = array_merge($events, $ledgerEvents);

        // Assemble narrative text
        $narrative = $assembler->assemble($events, $epoch);

        // Persist to chronicles table
        \Illuminate\Support\Facades\DB::table('chronicles')->insert([
            'world_id' => $world->id,
            'epoch' => $epoch,
            'content' => $narrative,
            'events' => json_encode($events),
            'created_at' => now(),
        ]);
    }

    /**
     * Detect simple events from world state for narrative generation
     */
    private function detectSimpleEvents(World $world, int $epoch, bool $isCollapse): array
    {
        $events = [];

        if ($isCollapse) {
            $reason = $this->getCollapseReason($world);
            $events[] = [
                'type' => 'collapse_warning',
                'severity' => 10,
                'narrative_template' => 'collapse_warning',
                'description' => $reason,
            ];
            return $events;
        }

        // Detect events based on world archetype values
        $archetypes = $world->archetypes ?? [];

        // Check for social tension
        $inequality = ($archetypes['social'] ?? 0.5);
        if ($inequality > 0.7) {
            $events[] = [
                'type' => 'social_tension',
                'severity' => (int)(($inequality - 0.5) * 20),
                'narrative_template' => 'social_tension',
            ];
        }

        // Check for famine
        $perception = ($archetypes['perception'] ?? 0.5);
        if ($perception < 0.3) {
            $events[] = [
                'type' => 'famine_crisis',
                'severity' => (int)((0.5 - $perception) * 20),
                'narrative_template' => 'famine_crisis',
            ];
        }

        // Check for foreign pressure
        $power = ($archetypes['power'] ?? 0.5);
        if ($power > 0.8) {
            $events[] = [
                'type' => 'foreign_pressure',
                'severity' => (int)(($power - 0.5) * 15),
                'narrative_template' => 'foreign_pressure',
            ];
        }

        // Check for collective trauma
        $metaphysical = ($archetypes['metaphysical'] ?? 0.5);
        if ($metaphysical > 0.7) {
            $events[] = [
                'type' => 'collective_trauma',
                'severity' => (int)(($metaphysical - 0.5) * 15),
                'narrative_template' => 'collective_trauma',
            ];
        }

        // Sudden change detection (significant drift)
        if ($epoch % 25 === 0 && rand(1, 100) <= 40) {
            $events[] = [
                'type' => 'sudden_change',
                'severity' => rand(5, 8),
                'narrative_template' => 'sudden_change',
            ];
        }

        // Default — quiet epoch
        if (empty($events)) {
            $events[] = [
                'type' => 'default',
                'severity' => 1,
                'narrative_template' => 'default',
            ];
        }

        return $events;
    }

    /**
     * Apply random drift to archetypes
     */
    private function applyRandomDrift(World $world): void
    {
        $weights = $this->archetypePool->getWeightsForWorld($world);
        
        // Get material influence on drift
        $materialDriftDeltas = $this->materialCoupler->applyMaterialInfluence($world);
        
        foreach ($weights as $weight) {
            // Random fluctuation (-0.05 to +0.05)
            $delta = (mt_rand(-50, 50) / 1000);
            
            // Add material influence if exists
            if (isset($materialDriftDeltas[$weight->archetype_key])) {
                $delta += $materialDriftDeltas[$weight->archetype_key];
            }
            
            $newWeight = $weight->weight + $delta;
            
            // Clamp
            $newWeight = max(0, min(1, $newWeight));
            
            if ($weight->weight != $newWeight) {
                $weight->weight = $newWeight;
                $weight->save();
            }
        }

        // Check if archetypes should activate dormant materials
        $this->materialCoupler->checkArchetypeActivation($world);
    }

    /**
     * Check if world collapses
     */
    public function checkCollapse(World $world): bool
    {
        $instability = $this->calculateGlobalInstability($world);
        
        // Threshold for collapse (1.0 = highly unstable)
        return $instability >= 1.0;
    }

    /**
     * Calculate Global Instability Score
     * Instability = (Archetype Imbalance) + (Historical Entropy) - (Institutional Resilience)
     */
    public function calculateGlobalInstability(World $world): float
    {
        // 1. Archetype Imbalance (Deviation from 0.5)
        $weights = $this->archetypePool->getWeightsForWorld($world);
        $deviation = 0.0;
        foreach ($weights as $w) {
            $deviation += abs($w->weight - 0.5);
        }
        $imbalance = ($weights->count() > 0) ? ($deviation / $weights->count()) * 2 : 0; // Scale to ~1.0

        // 2. Historical Entropy (Accumulated Scars)
        $impactService = app(\App\Domains\History\Services\ScarImpactService::class);
        $entropy = $impactService->calculateGlobalEntropyContribution($world, $world->tick);

        // 3. Institutional Resilience (Mitigation)
        $resilience = \App\Models\Institution::where('world_id', $world->id)
            ->get()
            ->sum(fn($i) => $i->authority_level * $i->public_trust);

        // Final Score
        $score = ($imbalance * 0.4) + ($entropy * 0.6) - ($resilience * 0.2);
        
        return max(0.0, $score);
    }

    /**
     * Determine the primary cause of collapse for narrative reporting
     */
    public function getCollapseReason(World $world): string
    {
        $weights = $this->archetypePool->getWeightsForWorld($world);
        $archetypes = \App\Domains\CognitiveKernel\Archetype::all()->keyBy('key');
        
        $domainDeviations = [
            'perception' => 0.0,
            'power' => 0.0,
            'social' => 0.0,
            'metaphysical' => 0.0,
        ];

        foreach ($weights as $w) {
            $archetype = $archetypes->get($w->archetype_key);
            if ($archetype && isset($domainDeviations[$archetype->domain])) {
                $domainDeviations[$archetype->domain] += abs($w->weight - 0.5);
            }
        }

        arsort($domainDeviations);
        $primary = array_key_first($domainDeviations);

        return match($primary) {
            'perception' => 'Họa đói kém đã bào mòn nhân tính, khiến trật tự vỡ vụn.',
            'social' => 'Xung đột giai cấp đã bùng nổ thành một cuộc đại nội chiến.',
            'power' => 'Sức ép từ ngoại bang đã vượt quá khả năng chịu đựng của vương quốc.',
            'metaphysical' => 'Những bóng ma của quá khứ đã quay lại nuốt chửng hiện tại.',
            default => 'Sự hỗn loạn đã vượt khỏi tầm kiểm soát của mọi phe phái.',
        };
    }

    /**
     * Create next world in saga
     */
    public function createNextWorld(Saga $saga): ?SagaWorld
    {
        if ($saga->current_world_index >= $saga->world_count) {
            $this->completeSaga($saga);
            return null;
        }

        // Get legacy from previous world
        $previousWorld = $saga->sagaWorlds()
            ->where('sequence', $saga->current_world_index - 1)
            ->first();

        $legacy = null;
        if ($previousWorld && $saga->carry_legacy) {
            $legacy = $this->legacyExtractor->extract($previousWorld);
        }

        // Check if world already exists for this sequence
        $existing = SagaWorld::where('saga_id', $saga->id)
            ->where('sequence', $saga->current_world_index)
            ->first();

        if ($existing) return $existing;

        // Create world with archetype bias
        $world = $this->createWorld($saga, $legacy);

        // Create saga world record
        $sagaWorld = SagaWorld::create([
            'saga_id' => $saga->id,
            'world_id' => $world->id,
            'sequence' => $saga->current_world_index,
            'archetype_legacy' => $legacy['archetype_legacy'] ?? null,
            'myth_legacy' => $legacy['myth_legacy'] ?? null,
            'status' => SagaWorld::STATUS_PENDING,
        ]);

        return $sagaWorld;
    }

    /**
     * Create world with archetype bias
     */
    private function createWorld(Saga $saga, ?array $legacy): World
    {
        $presetKey = $saga->preset_key ?? 'cuu_trong_thien';
        $preset = app(
            \App\Domains\Saga\Services\GenesisPresetService::class
        )->find($presetKey) ?? [];

        $world = World::create([
            'name' => "{$saga->name} - World {$saga->current_world_index}",
            'status' => 'active',
            'tick' => 0,
            'genre' => $preset['genre'] ?? $saga->genre ?? 'historical',
            'config' => [
                'preset_key' => $presetKey,
                'current_stage' => $preset['power_stage'] ?? 'mundane',
            ],
        ]);

        if (!empty($preset)) {
            app(\App\Domains\World\Services\WorldPowerProfileService::class)
                ->bootstrapProfile($world, $preset);
        }

        // Initialize archetypes with bias
        $archetypeFocus = $saga->archetype_focus ?? [];
        
        // Apply legacy bias if exists
        if ($legacy && isset($legacy['archetype_legacy'])) {
            $archetypeFocus = array_merge(
                $archetypeFocus,
                array_keys($legacy['archetype_legacy'])
            );
        }

        $this->archetypePool->initializeForWorld($world, $archetypeFocus);

        // Add pure random variance to prevent "clone worlds"
        // Mutate 1-2 random archetypes slightly
        $weights = $this->archetypePool->getWeightsForWorld($world);
        if ($weights->isNotEmpty()) {
            $victim = $weights->random(min(2, $weights->count()));
            foreach ($victim as $w) {
                $w->weight = max(0.1, min(0.9, $w->weight + (mt_rand(-20, 20) / 100)));
                $w->save();
            }
        }

        // Seed materials for this world
        $archetypeWeights = $this->archetypePool->getWeightsForWorld($world)
            ->pluck('weight', 'archetype_key')
            ->toArray();
        $this->materialSeeder->seedWorld($world, $archetypeWeights);

        return $world;
    }

    /**
     * Handle world completion
     */
    public function onWorldComplete(SagaWorld $sagaWorld, bool $collapsed = false): void
    {
        if ($collapsed) {
            $sagaWorld->markAsCollapsed([
                'legitimacy' => 0,
                'timestamp' => now()->toIso8601String()
            ]);
        } else {
            $sagaWorld->markAsCompleted();
        }

        $saga = $sagaWorld->saga;

        // Observe this world
        $this->observer->observe($saga, $sagaWorld);

        // Move to next world
        $saga->current_world_index++;
        $saga->save();

        $this->createNextWorld($saga);
    }

    /**
     * Complete saga
     */
    private function completeSaga(Saga $saga): void
    {
        $saga->status = Saga::STATUS_COMPLETED;
        $saga->completed_at = now();
        $saga->save();

        // Final saga-level observations
        $this->observer->observeSaga($saga);
    }

    /**
     * Get saga status
     */
    public function getStatus(Saga $saga): array
    {
        return [
            'id' => $saga->id,
            'name' => $saga->name,
            'status' => $saga->status,
            'progress' => [
                'current' => $saga->current_world_index,
                'total' => $saga->world_count,
                'percentage' => ($saga->current_world_index / $saga->world_count) * 100
            ],
            'worlds' => $saga->sagaWorlds()->get()->map(fn($sw) => [
                'sequence' => $sw->sequence,
                'status' => $sw->status,
                'collapsed' => $sw->hasCollapsed(),
            ]),
            'observations' => $saga->observations()->count(),
        ];
    }

    /**
     * Process material effects for the current tick
     */
    private function processMaterialEffects(World $world): void
    {
        $worldContext = [
            'tech_level' => 2, // Placeholder - would come from actual world state
            'active_materials' => [] // Placeholder
        ];

        $effects = $this->materialBridge->processTick($world, $worldContext);

        // Apply effects to world (placeholder - would modify actual world state)
        // For now, just log that materials are being processed
    }
    /**
     * Detect significant Ledger events for the current epoch
     */
    private function detectLedgerEvents(World $world, int $epoch): array
    {
        $events = [];
        // Fetch events from the ledger for this tick with high magnitude or specific types
        $ledgerEntries = \App\Models\WorldEvent::where('world_id', $world->id)
            ->where('tick', $epoch)
            ->get();

        foreach ($ledgerEntries as $entry) {
            // Only include significant events or specific implementation types
            if (($entry->payload['magnitude'] ?? 0) >= 0.5 
                || in_array($entry->type, ['stage_transition', 'world_collapse', 'personal_event'])) {
                
                $events[] = [
                    'type' => 'ledger_event',
                    'severity' => ($entry->type === 'personal_event') ? 2 : 10, // Lower severity for personal events
                    'original_event' => $entry,
                    'narrative_template' => ($entry->type === 'personal_event') ? 'personal_history' : 'epic_history'
                ];
            }
        }
        
        return $events;
    }
}
