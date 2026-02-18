<?php

namespace App\Console\Commands;

use App\Domains\Cosmology\Contracts\CivilizationSnapshotRepositoryInterface;
use App\Domains\Cosmology\Cosmology;
use App\Domains\Cosmology\Services\AttractorInfluenceRecorder;
use App\Domains\Cosmology\Services\LifecycleService;
use App\Models\UniverseModel;
use Illuminate\Console\Command;

class CosmologyCycleCommand extends Command
{
    private const SNAPSHOT_EVERY_N_TICKS = 10;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cosmology:cycle {--async : Dispatch TickUniverseJob per universe instead of ticking in-process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ticks all universes and manages lifecycle (Birth/Death)';

    /**
     * Execute the console command.
     */
    public function handle(
        LifecycleService $lifecycleService,
        \App\Domains\Cosmology\Services\CrisisService $crisisService,
        \App\Domains\Cosmology\Services\ConvergenceService $convergenceService,
        \App\Domains\Cosmology\Services\BifurcationService $bifurcationService,
        \App\Domains\Cosmology\Services\AnomalyService $anomalyService,
        \App\Domains\Cosmology\Services\FactionService $factionService,
        \App\Domains\Cosmology\Services\ConflictService $conflictService,
        \App\Domains\Cosmology\Services\HarbingerService $harbingerService,
        ?CivilizationSnapshotRepositoryInterface $snapshotRepo = null,
        ?AttractorInfluenceRecorder $influenceRecorder = null
    ) {
        $this->info('Starting Grand Cycle...');

        // Ensure default factions exist (Phase 19)
        $factionService->ensureCommonFactionsExist();

        // 0. Potential Regional Crisis (Phase 15)
        if (mt_rand(1, 100) <= 10) { // 10% chance
            $types = ['VOID_LEAK', 'CHRONO_STORM', 'ORDER_RESONANCE'];
            $type = $types[array_rand($types)];
            $center = ['x' => mt_rand(-800, 800), 'y' => mt_rand(-800, 800), 'z' => mt_rand(-800, 800)];
            $radius = mt_rand(100, 400);

            $this->warn("!!! MULTIVERSE CRISIS DETECTED: {$type} at center [" . implode(',', $center) . "] radius {$radius} !!!");
            $affected = $crisisService->triggerRegionalCrisis($center, $radius, $type);
            $this->warn("Crisis affected " . count($affected) . " universes.");
        }
        
        // 1. Fetch Active Universes
        $activeUniverses = UniverseModel::where('is_archived', false)->get();
        $this->info("Found {$activeUniverses->count()} active universes.");

        if ($this->option('async')) {
            foreach ($activeUniverses as $model) {
                \App\Jobs\TickUniverseJob::dispatch($model->id, 1);
            }
            $this->info('Dispatched ' . $activeUniverses->count() . ' TickUniverseJob(s).');
            // Spawn new and global threat below
        } else {
            $cosmology = Cosmology::boot();
            $repo = app(\App\Domains\Cosmology\Repositories\CosmologyRepository::class);
            $coupling = app(\App\Domains\Cosmology\Services\CouplingService::class);

        foreach ($activeUniverses as $model) {
            $universe = $repo->find($model->id);
            
            // CHECK ANOMALIES (Phase 18)
            $anomalyType = $anomalyService->triggerPotentialAnomaly($universe);
            if ($anomalyType) {
                $this->warn("Universe {$universe->getId()} is experiencing a {$anomalyType}!");
                $universe = $anomalyService->applyAnomaly($universe, $anomalyType);
                $repo->save($universe);
            }

            // APPLY FACTION BUFFS (Phase 19)
            $universe = $factionService->applyFactionBuffs($universe);

            // APPLY FACTION EDICTS (Phase 21)
            $universe = $factionService->applyEdicts($universe);

            // APPLY INTER-FACTION FRICTION (Phase 21)
            $universe = $conflictService->applyFriction($universe, $activeUniverses->all());

            // APPLY VOID INCURSION (Phase 22)
            $universe = $harbingerService->applyVoidIncursion($universe);

            // AUTO-JOIN FACTION (Phase 19 - if none exists)
            if (!$universe->getCosmicFactionId()) {
                $suggestedFaction = $factionService->getSuggestedFaction($universe);
                if ($suggestedFaction) {
                    $this->info("Universe {$universe->getId()} joined {$suggestedFaction}");
                    $factionService->joinFaction($universe, $suggestedFaction);
                    // Reload to get the ID for next tick
                    $universe = $repo->find($universe->getId());
                }
            }

            // Capture state/age before tick for snapshot/diff (Phase 3.1)
            $stateBeforeTick = $universe->getState();
            $ageBeforeTick = $universe->getAge();

            // TICK
            $cosmology = Cosmology::boot();
            $cosmology->getFieldSpace()->addUniverse($universe);
            $cosmology->tick();

            // COUPLING (Simplified for command)
            $neighbors = collect([]);
            if ($activeUniverses->count() > 1) {
                // Determine sample size
                $sampleSize = min(3, $activeUniverses->count() - 1);
                
                // Fetch random models excluding current
                $neighborModels = $activeUniverses->where('id', '!=', $model->id);
                
                if ($neighborModels->count() >= $sampleSize) {
                    $randomNeighbors = $neighborModels->random($sampleSize);
                    
                    foreach ($randomNeighbors as $n) {
                        $neighbors->push($repo->find($n->id));
                    }
                }
            }
            
            // Convert collection to array for service
            $couplingResult = $coupling->interact($universe, $neighbors->all(), 0.05);
            if ($couplingResult) {
                 $universe = new \App\Domains\Cosmology\Entities\Universe(
                     $couplingResult, 
                     $universe->getParameters(), // Use parameters
                     $universe->getId(), 
                     $universe->getAge()
                 );
            }
            
            // CHECK BIFURCATION (Phase 16)
            if (!$model->is_archived && $universe->getAge() > 200 && $universe->getState()->getInnovation() > 0.8 && mt_rand(1, 100) <= 5) {
                $this->warn("Universe {$universe->getId()} is BIFURCATING!");
                $branches = $bifurcationService->split($universe);
                $lifecycleService->archive($universe, 'BIFURCATION');
                $model->is_archived = true; // Mark locally as well
                continue;
            }

            // CHECK CONVERGENCE (Phase 16)
            if (!$model->is_archived && $activeUniverses->count() > 1) {
                foreach ($neighborModels as $nModel) {
                    if ($nModel->is_archived) continue;

                    $neighbor = $repo->find($nModel->id);
                    if (!$neighbor) continue;

                    $spatialDist = $coupling->calculateSpatialDistance($universe, $neighbor);
                    $vectorDist = $coupling->calculateDistance($universe, $neighbor);

                    if ($spatialDist < 50 && $vectorDist < 0.5) {
                        $this->warn("Universes {$universe->getId()} and {$neighbor->getId()} are CONVERGING!");
                        $convergenceService->merge($universe, $neighbor);
                        $model->is_archived = true;
                        $nModel->is_archived = true; // Mark both as archived to avoid double processing
                        break;
                    }
                }
                if ($model->is_archived) continue;
            }

            // CHECK DEATH
            $cause = $lifecycleService->checkDeath($universe);
            if ($cause) {
                $this->error("Universe {$universe->getId()} has died of {$cause}.");
                $lifecycleService->archive($universe, $cause);
            } else {
                // Phase 3.1: persist diff and periodic snapshot
                if ($snapshotRepo !== null) {
                    $stateAfter = $universe->getState();
                    $ageAfter = $universe->getAge();
                    $diff = $stateAfter->subtract($stateBeforeTick)->getAll();
                    $snapshotRepo->saveDiff($universe->getId(), $ageBeforeTick, $ageAfter, $diff);
                    if ($ageAfter > 0 && $ageAfter % self::SNAPSHOT_EVERY_N_TICKS === 0) {
                        $snapshotRepo->saveSnapshot($universe->getId(), $ageAfter, null, null, $stateAfter);
                    }
                    if ($influenceRecorder !== null) {
                        $influenceRecorder->record($universe->getId(), $ageAfter, $stateAfter);
                    }
                }
                $repo->save($universe);
                $this->info("Universe {$universe->getId()} ticked. Age: {$universe->getAge()}");
            }
        }
        }

        // 2. Spawn New if needed (each Universe must belong to a World)
        $activeCount = UniverseModel::where('is_archived', false)->count();
        if ($activeCount < 5) {
            $worldId = \App\Models\World::orderBy('id')->value('id');
            if ($worldId === null) {
                $this->warn('No World exists; cannot spawn new Universe. Create a World first.');
            } else {
                $toSpawn = 5 - $activeCount;
                $this->info("Spawning {$toSpawn} new universes to maintain balance.");
                for ($i = 0; $i < $toSpawn; $i++) {
                    $newUniv = $lifecycleService->spawnNew((int) $worldId);
                    $this->info("Ignited Universe: {$newUniv->getId()}");
                }
            }
        }
        
        // Global Threat Processing (Phase 22)
        $harbingerService->processGlobalThreat($activeUniverses->all());

        $this->info('Grand Cycle completed.');
    }
}
