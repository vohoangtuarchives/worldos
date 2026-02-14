<?php

namespace App\Domains\Saga;

use App\Domains\CognitiveKernel\ArchetypePool;
use App\Domains\Material\MaterialSeeder;
use App\Domains\Material\MaterialWorldBridge;
use App\Domains\Material\MaterialArchetypeCoupler;
use App\Models\World;
use App\Domains\Cosmic\Services\WorldEvolutionPipeline;
use App\Domains\Cosmic\Contracts\CosmicSnapshotRepositoryInterface;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\EnvironmentState;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Cosmic\ValueObjects\Attractor;

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
    private \App\Domains\Saga\Services\SagaDirector $director;
    private ?WorldEvolutionPipeline $evolutionPipeline;
    private ?CosmicSnapshotRepositoryInterface $snapshotRepo;

    public function __construct(
        MaterialSeeder $materialSeeder = null,
        MaterialWorldBridge $materialBridge = null,
        MaterialArchetypeCoupler $materialCoupler = null,
        \App\Domains\Saga\Services\SagaDirector $director = null,
        ?WorldEvolutionPipeline $evolutionPipeline = null,
        ?CosmicSnapshotRepositoryInterface $snapshotRepo = null
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
        $this->director = $director ?? app(\App\Domains\Saga\Services\SagaDirector::class);
        $this->evolutionPipeline = $evolutionPipeline ?? app(WorldEvolutionPipeline::class);
        $this->snapshotRepo = $snapshotRepo ?? app(CosmicSnapshotRepositoryInterface::class);
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
            // Evaluate multiverse pressure periodically
            $this->director->evaluateSaga($saga);
            
            
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
        $maxYears = 100.0; // Target duration in years
        $chronicleInterval = 5.0; // Write chronicle every 5 years (approx)
        $lastChronicleTime = $world->current_time;

        $maxYears = $this->director ? $this->director->determineWorldDuration($sagaWorld) : 1000;

        // Initialize Factions
        $this->ensureFactionsInitialized($world);

        // Initialize Narrative & Agents
        $authorRegistry = app(\App\Domains\Saga\Author\AuthorRegistry::class);
        $personaKey = $world->config['author_persona'] ?? $sagaWorld->archetype_id ?? 'System';
        $persona = $authorRegistry->get($personaKey);
        
        // Fallback if not found
        if (!$persona) {
            $persona = $authorRegistry->get('System') ?? $authorRegistry->get('WuxiaMaster');
        }
        $narrativeAssembler = app(\App\Domains\Saga\CausalNarrativeAssembler::class);
        $agentOrchestrator = app(\App\Domains\Faction\Services\FactionAgent::class);
        $conflictResolver = app(\App\Domains\Faction\Services\ConflictResolver::class);
        
        if ($narrativeAssembler) {
            $narrativeAssembler->setPersona($persona);
        }

        // Initialize State (DCE Integration)
        $currentSnapshot = $this->loadOrInitializeState($world);
        $deltaYears = 1; // 1 year per tick/step

        // Dynamic Time Loop
        while ($world->current_time < $maxYears) {
            // Check for Pause/Freeze signal
            if (!$world->refresh()->isAutonomous()) {
                // If paused, we just exit the loop. The state is preserved in DB.
                // Next resume will reload from DB via loadOrInitializeState
                return; 
            }

            if ($this->evolutionPipeline) {
                // --- NEW ENGINE: DCE ---
                $externalModifiers = [];
                
                // VIETNAMESE ORIGIN: Calculate Civilization Boosts from Heroes
                if ($world->origin_type === 'vietnamese') {
                    $csmService = app(\App\Domains\Vietnamese\Services\CosmicIntegrationService::class);
                    $currentEra = (int) floor(($world->current_time ?? 0) / 50);
                    $boosts = $csmService->calculateEraCivilizationBoost($currentEra);
                    
                    // Map Dimensions to Pipeline Modifiers
                    // Governance -> Stability
                    if (!empty($boosts['governance'])) {
                        $externalModifiers['stability_modifier'] = $boosts['governance']; // Direct additive
                    }
                    // Culture/Philosophy -> Knowledge Growth
                    if (!empty($boosts['culture']) || !empty($boosts['philosophy'])) {
                        $externalModifiers['knowledge_growth_factor'] = ($boosts['culture'] ?? 0) + ($boosts['philosophy'] ?? 0);
                    }
                    // Military -> Entropy Resistance
                    if (!empty($boosts['military'])) {
                        $externalModifiers['entropy_resistance'] = $boosts['military'];
                    }
                    // Education -> Efficiency Bonus
                    if (!empty($boosts['education'])) {
                        $externalModifiers['efficiency_bonus'] = $boosts['education'];
                    }

                    // --- REALM CONTACT INFLUENCE ---
                    $realmService = app(\App\Domains\Vietnamese\Services\RealmContactService::class);
                    $realmMods = $realmService->calculateRealmInfluence($world);

                    // Merge Realm Modifiers
                    if (!empty($realmMods['military_pressure'])) {
                        // Pressure increases strain but might prompt military response
                        $externalModifiers['strain_modifier'] = ($externalModifiers['strain_modifier'] ?? 0) + $realmMods['military_pressure'];
                    }
                    if (!empty($realmMods['cultural_assimilation'])) {
                        // Assimilation reduces ritual coherence (mapped to stability penalty or specific param)
                        // For now, let's say it reduces stability slightly if high
                        $externalModifiers['stability_modifier'] = ($externalModifiers['stability_modifier'] ?? 0) - ($realmMods['cultural_assimilation'] * 0.2);
                    }
                    if (!empty($realmMods['trade_bonus'])) {
                         $externalModifiers['efficiency_bonus'] = ($externalModifiers['efficiency_bonus'] ?? 0) + $realmMods['trade_bonus'];
                    }
                    if (!empty($realmMods['instability'])) {
                         $externalModifiers['stability_modifier'] = ($externalModifiers['stability_modifier'] ?? 0) - $realmMods['instability'];
                    }
                }

                $nextSnapshot = $this->evolutionPipeline->step($currentSnapshot, 0.0, $deltaYears, $externalModifiers);
                
                // Persist snapshot
                if ($this->snapshotRepo) {
                    $this->snapshotRepo->saveSnapshot($world->id, $nextSnapshot);
                    
                    // Persist events from the step
                    foreach ($this->evolutionPipeline->getLastStepEvents() as $event) {
                        $this->snapshotRepo->saveEvent($world->id, $event);
                    }
                }

                // Update World Model for backward compatibility / UI
                $world->current_time = $nextSnapshot->year;
                $world->entropy = $nextSnapshot->cosmic->entropy;
                // $world->energy? Not in schema yet, UI might need `cosmic_snapshots` table
                $world->save();

                // Advance state for next iteration
                $prevCiv = $currentSnapshot->civilization;
                $currentSnapshot = $nextSnapshot;

            } else {
                 // Legacy Fallback (if pipeline not injected)
                 $this->step($world->id, 1);
                 $prevCiv = null; // Unhandled in legacy
            }

            if ($world->origin_type === 'vietnamese') {
                $bifService = app(\App\Domains\Vietnamese\Services\HeroBifurcationService::class);
                $currentEra = (int) floor($world->current_time / 50);
                
                // Check for bifurcation triggers at every step
                $bifResult = $bifService->checkHeroTriggers($world, $currentEra);
                
                if ($bifResult) {
                    $this->ledger->record($world, 'world_bifurcation', 
                        "Bifurcation triggered by {$bifResult['trigger_hero']}", 
                        1.0, 1.0
                    );
                    
                    // Stop simulation of this timeline as it has split
                    $this->onWorldComplete($sagaWorld, false); 
                    return; 
                }
            }

            // After step, refresh world state to get latest values
            $world->refresh();

            // 1. Agent Decision Phase
            $factions = $world->factions;
            $intents = [];
            foreach ($factions as $faction) {
                $agentOrchestrator->executeTurn(
                    $faction, 
                    $world, 
                    $world->tick,
                    $currentSnapshot->cosmic,
                    $currentSnapshot->civilization
                ); 
                $intents[$faction->id] = \App\Domains\Faction\Enums\FactionIntentType::from($faction->attributes['current_intent'] ?? 'survive');
            }

            // 2. Conflict Resolution Phase
            $conflictResolver->resolve($world, $intents);

            // 3. Process Material Effects (Passed deltaTime)
            $this->processMaterialEffects($world, $deltaYears ?? 1.0);

            // 4. Outcome Recording Phase
            foreach ($factions as $faction) {
                $faction->refresh();
                $reward = $faction->attributes['tick_reward'] ?? 0.0;
                $reasoning = $faction->attributes['tick_reason'] ?? [];
                $agentOrchestrator->recordOutcome($faction, $world->tick, $reward, $reasoning);
            }
            // 5. Evolution Phase (Ledger & Stage)
            $this->transitionEngine->evaluateTransition($world);

            // Check for potential collapse (random chance for chaos)
            if ($this->checkCollapse($world)) {
                // Record Collapse in Ledger
                $reason = $this->getCollapseReason($world);
                $this->ledger->record($world, 'world_collapse', $reason, 1.0, 1.0);

                // Write final chronicle before collapse
                $this->writeChronicle(
                    $world, 
                    $world->tick, 
                    $narrativeAssembler, 
                    true,
                    $currentSnapshot->cosmic,
                    $currentSnapshot->civilization,
                    $prevCiv ?? null
                );
                $this->onWorldComplete($sagaWorld, true);
                return;
            }

            // Write chronicle at intervals (based on Time, not Ticks)
            if (($world->current_time - $lastChronicleTime) >= $chronicleInterval) {
                $this->writeChronicle(
                    $world, 
                    $world->tick, 
                    $narrativeAssembler, 
                    false,
                    $currentSnapshot->cosmic,
                    $currentSnapshot->civilization,
                    $prevCiv ?? null
                );
                $lastChronicleTime = $world->current_time;
            }
        }

        $world->save();
        
        // Only mark complete if we finished the duration
        if ($world->current_time >= $maxYears) {
            $this->onWorldComplete($sagaWorld, false);
        }
    }

    /**
     * Load the latest snapshot or create a fresh genesis state.
     */
    private function loadOrInitializeState(World $world): WorldSnapshot
    {
        // Try to load latest snapshot from repo
        if ($this->snapshotRepo) {
            $latest = $this->snapshotRepo->latestSnapshot($world->id);
            if ($latest) {
                return $latest;
            }
        }

        // If no snapshot exists (Genesis), create default state
        
        return new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.3,
                energy: 0.5,
                causality: 0.1,
                strain: 0.0,
                stability: 0.8,
                currentAttractor: 'EQUILIBRIUM',
                year: $world->current_time ?? 0
            ),
            environment: EnvironmentState::defaultObservation($world->current_time ?? 0),
            civilization: CivilizationState::defaultObservation($world->current_time ?? 0),
            year: $world->current_time ?? 0
        );
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
    private function writeChronicle(
        World $world, 
        int $epoch, 
        CausalNarrativeAssembler $assembler, 
        bool $isCollapse,
        CosmicState $cosmic,
        CivilizationState $civ,
        ?CivilizationState $prevCiv = null
    ): void {
        // Build simple events from current world state
        $events = $this->detectSimpleEvents($world, $epoch, $isCollapse, $cosmic, $civ);
        
        // Merge with explicit Ledger events (Epic History)
        $ledgerEvents = $this->detectLedgerEvents($world, $epoch);
        $events = array_merge($events, $ledgerEvents);

        // Assemble narrative text using Causal Engine
        $narrative = $assembler->assemble($events, $epoch, $cosmic, $civ);

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
    private function detectSimpleEvents(
        World $world, 
        int $epoch, 
        bool $isCollapse,
        CosmicState $cosmic,
        CivilizationState $civ
    ): array {
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

        // --- NEW: Semantic Conditions based on Thermodynamic State ---

        // 1. Scientific Breakthrough (High Knowledge + Tech)
        if ($civ->culturalEnergy > 1.5 || $civ->technologicalLevel > 1.2) {
            // Chance to trigger if not too chaotic
            if ($cosmic->entropy < 0.7 && rand(1, 100) <= 30) {
                $events[] = [
                    'type' => 'scientific_breakthrough',
                    'severity' => ($civ->culturalEnergy > 2.0) ? 3 : 2,
                    'narrative_template' => 'scientific_breakthrough',
                ];
            }
        }

        // 2. Religious Schism (Low Ritual + High Strain)
        if ($civ->spiritualCohesion < 0.3 && $cosmic->strain > 0.4) {
            if (rand(1, 100) <= 40) {
                $events[] = [
                    'type' => 'religious_schism',
                    'severity' => ($cosmic->strain > 0.7) ? 3 : 2,
                    'narrative_template' => 'religious_schism',
                ];
            }
        }

        // 3. Cultural Renaissance (High Resilience + Stability)
        if ($civ->resilience > 0.8 && $cosmic->stability > 0.7) {
            if (rand(1, 100) <= 30) {
                $events[] = [
                    'type' => 'cultural_renaissance',
                    'severity' => ($civ->resilience > 0.9) ? 3 : 2,
                    'narrative_template' => 'cultural_renaissance',
                ];
            }
        }

        // 4. Resource Crisis (Environemntal impact - inferred for now via Entropy/Strain if no EnvState passed)
        // Ideally we should pass EnvironmentState too, but we can proxy with Entropy > 0.6 AND Stability < 0.4
        if ($cosmic->entropy > 0.6 && $cosmic->stability < 0.4) {
             if (rand(1, 100) <= 35) {
                $events[] = [
                    'type' => 'resource_crisis',
                    'severity' => ($cosmic->entropy > 0.8) ? 3 : 2,
                    'narrative_template' => 'resource_crisis',
                ];
            }
        }

        // 5. Social Class Dynamics
        foreach ($civ->socialClasses as $class) {
            // Merchant Uprising (High Power + Low Contentment)
            if ($class->type === \App\Domains\Cosmic\Enums\SocialClassType::MERCHANT && $class->power > 0.7 && $class->contentment < 0.3) {
                if (rand(1, 100) <= 25) {
                    $events[] = [
                        'type' => 'merchant_uprising',
                        'severity' => 3,
                        'narrative_template' => 'merchant_uprising',
                    ];
                }
            }
            // Nobility Collapse (Low Power in Chaos)
            if ($class->type === \App\Domains\Cosmic\Enums\SocialClassType::NOBILITY && $class->power < 0.2 && $cosmic->entropy > 0.7) {
                 $events[] = [
                    'type' => 'nobility_collapse',
                    'severity' => 2,
                    'narrative_template' => 'nobility_collapse',
                ];
            }
            // Warrior Dominance (High Power in Instability)
            if ($class->type === \App\Domains\Cosmic\Enums\SocialClassType::WARRIOR && $class->power > 0.8 && $cosmic->stability < 0.4) {
                 $events[] = [
                    'type' => 'warrior_dominance',
                    'severity' => 3,
                    'narrative_template' => 'warrior_dominance',
                ];
            }
        }

        // --- Legacy Logic (Archetype based) ---
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

        // Check for famine (low perception/resource management)
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
        // Use deterministic content check instead of rand() for variety if possible, but rand() here is for EVENT GENERATION not prose
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
        // Check for specific origin
        $originType = $saga->metadata['origin_type'] ?? 'cosmic';

        if ($originType === 'vietnamese') {
             return app(\App\Domains\Vietnamese\Services\VietnameseOriginService::class)
                 ->createVietnameseWorld([
                     'name' => "{$saga->name} - World {$saga->current_world_index}",
                     'chaos_seed' => mt_rand(1, 999999),
                     'initial_entropy' => 0.8, // Default high entropy for mythos
                     'initial_energy' => 0.9,
                 ]);
        }

        $presetKey = $saga->preset_key ?? 'cuu_trong_thien';
        $preset = app(
            \App\Domains\Saga\Services\GenesisPresetService::class
        )->find($presetKey) ?? [];

        $baseName = "{$saga->name} - World {$saga->current_world_index}";
        $name = $baseName;
        $counter = 1;
        while (World::where('name', $name)->exists()) {
            $name = "{$baseName} ({$counter})";
            $counter++;
        }

        $world = World::create([
            'name' => $name,
            'status' => 'active',
            'tick' => 0,
            'autonomous' => true,
            'preset' => $presetKey, // Added preset
            'gene_vector' => $preset['gene_vector'] ?? [], // Added gene_vector
            'origin_type' => 'cosmic',
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
    private function processMaterialEffects(World $world, float $deltaTime): void
    {
        $effects = $this->materialBridge->processTick($world, $deltaTime);

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
