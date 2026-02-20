<?php

namespace App\Http\Controllers\WriterConsole;

use App\Http\Controllers\Controller;
use App\Models\World;
use App\Domains\Cosmic\Services\MetricsSnapshotService;
use Tuzy\Domain\World\Exception\WorldNotFoundException;
use App\Domains\Cosmic\Services\AlertEvaluationEngine;
use App\Domains\Cosmic\Services\EpochControlService;
use App\Domains\Cosmic\Services\EmergencyInterventionService;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\Material;
use App\Domains\World\Myth;
use App\Domains\World\Scar;
use App\Domains\Power\Services\WorldPressureService;
use App\Domains\World\Services\EventGate;
use App\Domains\World\ValueObjects\PhysicsProfile;
use App\Models\GateChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorldHubController extends Controller
{
    public function __construct(
        private MetricsSnapshotService $metricsService,
        private AlertEvaluationEngine $alertEngine,
        private EpochControlService $epochControl,
        private EmergencyInterventionService $emergencyService,
        private MaterialRepositoryInterface $materialRepo,
        private WorldPressureService $pressureService,
        private EventGate $eventGate,
        private ?\App\Domains\Cosmic\Contracts\AttractorRepositoryInterface $attractorRepo = null,
    ) {}

    /**
     * GET /writer/worlds/{worldId}?tab=overview
     * Unified World Hub — 5-tab view.
     */
    public function show(string $worldId, Request $request): View
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $tab = $request->query('tab', 'overview');

        // Base data for all tabs
        $data = [
            'world' => $world,
            'activeTab' => $tab,
            'socialClasses' => $this->getLatestSocialClasses($world),
        ];

        // Load tab-specific data
        $data = match ($tab) {
            'cosmic' => array_merge($data, $this->loadCosmicData($world)),
            'materials' => array_merge($data, $this->loadMaterialsData($world)),
            'story' => array_merge($data, $this->loadStoryData($world)),
            'controls' => array_merge($data, $this->loadControlsData($world)),
            'social' => array_merge($data, $this->loadSocialData($world)),
            'heroes' => array_merge($data, $this->loadHeroesData($world)),
            default => array_merge($data, $this->loadOverviewData($world)),
        };

        return view('writer.world-hub.index', $data);
    }

    // ─── TAB: OVERVIEW ─────────────────────────────────────────────

    private function loadOverviewData(World $world): array
    {
        // Archetypes/themes via WriterFacingAPI
        $technicalState = [
            'archetypes' => [],
            'legitimacy' => 1.0,
        ];
        try {
            $writerApi = app(\App\Domains\WriterConsole\WriterFacingAPI::class);
            $writerState = $writerApi->getWorldState($technicalState);
        } catch (\Throwable) {
            $writerState = ['stability' => 'Unknown', 'tension' => 'N/A', 'themes' => []];
        }

        // Chronicles
        $chronicles = DB::table('chronicles')
            ->where('world_id', $world->id)
            ->orderBy('epoch', 'asc')
            ->limit(20)
            ->get();

        // Gates (Multiverse Rifts)
        $gates = GateChannel::where('source_world_id', $world->id)
            ->orWhere('target_world_id', $world->id)
            ->with(['sourceWorld', 'targetWorld'])
            ->get();

        // Reality Drift
        $baseline = $world->preset === 'void'
            ? PhysicsProfile::void()
            : PhysicsProfile::standard();
        
        try {
            $drift = $world->physics_profile->calculateDrift($baseline);
        } catch (\Throwable) {
            $drift = 0;
        }

        return [
            'writerState' => $writerState,
            'chronicles' => $chronicles,
            'gates' => $gates,
            'realityDrift' => $drift,
        ];
    }

    // ─── TAB: COSMIC OBSERVATORY ────────────────────────────────────

    private function loadCosmicData(World $world): array
    {
        $snapshot = $world->cosmicSnapshots()->latest('year')->first();

        $emptyMetrics = [
            'stability' => ['ssi' => 0, 'cf' => 0, 'stability_margin' => 0],
            'evolution' => ['di' => 0, 'agr' => 0, 'res' => 0],
            'power' => ['icr' => 0, 'vps' => 0, 'api' => 0],
            'memory' => ['hbr' => 0, 'cm' => 0, 'mde' => 0],
            'emergence' => ['epi' => 0, 'atr' => 0],
            'governance' => ['par' => 0, 'gl' => 0, 'hii' => 0],
            'meta' => ['chs' => 0],
            'severity' => 'HEALTHY',
            'critical_four' => ['SSI' => 0, 'DI' => 0, 'CF' => 0, 'HBR' => 0],
        ];

        if (!$snapshot) {
            return [
                'currentEpoch' => 0,
                'metrics' => $emptyMetrics,
                'alerts' => [],
                'composites' => [],
                'attractors' => [],
                'stateVector' => [0, 0, 0, 0, 0],
                'trajectoryLabels' => [],
                'trajectoryEntropy' => [],
                'trajectoryStability' => [],
                'trajectoryStrain' => [],
                'trajectoryEnergy' => [],
                'metaLayer' => null, // Placeholder
            ];
        }

        // Fetch Meta Layer State
        $metaState = \App\Models\MetaLayerState::instance();
        
        // Metrics
        $trajectoryModels = $world->cosmicSnapshots()->latest('year')->limit(50)->get()->reverse()->values();
        
        // Convert Eloquent models to Domain Value Objects
        $repo = app(\App\Domains\Cosmic\Contracts\CosmicSnapshotRepositoryInterface::class);
        $currentSnapshotVO = $repo->snapshotAt($world->id, $snapshot->year);
        
        $trajectoryVOs = $trajectoryModels->map(function ($model) use ($repo) {
             return $repo->snapshotAt($model->world_id, $model->year);
        })->toArray();

        $metricsSnapshot = $this->metricsService->calculate($currentSnapshotVO, $trajectoryVOs);
        $metricsArray = $metricsSnapshot->toArray();

        // Alerts
        $alertResult = $this->alertEngine->evaluate($metricsSnapshot, $snapshot->year);

        // Attractors + Attractor Memory Data
        $cosmic = $currentSnapshotVO->cosmic;
        $attractorCatalog = \App\Domains\Cosmic\ValueObjects\Attractor::catalog();
        $attractors = [];
        foreach ($attractorCatalog as $att) {
            $attractors[] = [
                'code' => $att->code,
                'is_current' => $att->code === $cosmic->currentAttractor,
                'equilibrium' => [
                    'entropy' => $att->equilibriumEntropy,
                    'energy' => $att->equilibriumEnergy,
                    'stability' => 0.7, // Proxy default
                ],
                'distance_to_current' => $this->calculateDistance($cosmic, $att),
                'transitions' => $att->transitionsTo,
            ];
        }

        // NEW: Attractor Memory Data
        $attractorMemory = $this->getAttractorMemoryData($currentSnapshotVO);
        $incarnationTree = $this->getIncarnationTree($cosmic->currentAttractor ?? 'EQUILIBRIUM');

        // Trajectory chart data
        $rawSnapshots = $trajectoryModels;
        $trajectoryLabels = $rawSnapshots->map(fn($s) => $s->year)->toArray();
        $trajectoryEntropy = $rawSnapshots->map(fn($s) => $s->entropy ?? 0)->toArray(); // Fixed: use model attribute
        $trajectoryStability = $rawSnapshots->map(fn($s) => $s->stability ?? 0)->toArray();
        $trajectoryStrain = $rawSnapshots->map(fn($s) => $s->tension ?? 0)->toArray(); // Map tension -> strain
        $trajectoryEnergy = $rawSnapshots->map(fn($s) => $s->energy ?? 0)->toArray();

        // Calculate Narrative Archetype (Replicating CausalNarrativeAssembler logic)
        $strain = $snapshot->tension ?? 0;
        $resilience = $snapshot->civ_resilience ?? 1.0;
        $entropy = $snapshot->entropy ?? 0;
        $energy = $snapshot->energy ?? 0;
        $stability = $snapshot->stability ?? 0;

        $archetype = 'neutral';
        if ($strain > 0.8 || $resilience < 0.2) {
            $archetype = 'collapse';
        } elseif ($entropy > 0.6 && $resilience > 0.5) {
            $archetype = 'turbulence';
        } elseif ($entropy < 0.3 && $energy < 0.3) {
            $archetype = 'stagnation';
        } elseif ($stability > 0.6 && $energy > 0.5 && $resilience > 0.7) {
            $archetype = 'golden_age';
        }

        return [
            'currentEpoch' => $snapshot->year,
            'metrics' => $metricsArray,
            'alerts' => $alertResult['alerts'] ?? [],
            'composites' => $alertResult['composites'] ?? [],
            'attractors' => $attractors,
            'attractorMemory' => $attractorMemory,        // NEW
            'incarnationTree' => $incarnationTree,        // NEW
            'stateVector' => [
                $snapshot->entropy,
                $snapshot->energy,
                $snapshot->stability,
                $snapshot->tension, // Strain
                $snapshot->resonance, // Causality/Resonance
            ],
            'thermo' => [
                'resilience' => $resilience,
                'strain' => $strain,
                'archetype' => $archetype,
            ],
            'trajectoryLabels' => $trajectoryLabels,
            'trajectoryEntropy' => $trajectoryEntropy,
            'trajectoryStability' => $trajectoryStability,
            'trajectoryStrain' => $trajectoryStrain,
            'trajectoryEnergy' => $trajectoryEnergy,
            
            // Autonomous Meta Layer
            'metaLayer' => [
                'chaos' => $metaState->chaos_pool,
                'entropy_pressure' => $metaState->entropy_pressure,
                'ideology' => $metaState->ideology_vector,
                'era' => $metaState->current_era_index,
                'stability' => $metaState->stability_index,
                'flux' => $metaState->resource_flux,
            ],
        ];
    }

    // ─── TAB: MATERIALS ─────────────────────────────────────────────

    private function loadMaterialsData(World $world): array
    {
        return [
            'instances' => $this->materialRepo->getInstancesForWorld($world->id),
            'availableMaterials' => Material::all(),
        ];
    }

    // ─── TAB: STORY ─────────────────────────────────────────────────

    private function loadStoryData(World $world): array
    {
        $chronicles = DB::table('chronicles')
            ->where('world_id', $world->id)
            ->orderBy('epoch', 'asc')
            ->get();

        $story = \App\Models\Story::where('world_id', $world->id)->first();

        return [
            'chronicles' => $chronicles,
            'story' => $story,
        ];
    }

    // ─── TAB: CONTROLS ──────────────────────────────────────────────

    private function loadControlsData(World $world): array
    {
        $powerState = DB::table('world_power_stages')
            ->where('world_id', $world->id)
            ->first();

        $currentPressure = $this->pressureService->calculatePressure($world->id);

        $myths = Myth::where('world_id', $world->id)->get();
        $scars = Scar::where('world_id', $world->id)->get();

        $recentEvents = DB::table('world_event_ledger')
            ->where('world_id', $world->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return [
            'powerState' => $powerState,
            'currentPressure' => $currentPressure,
            'myths' => $myths,
            'scars' => $scars,
            'recentEvents' => $recentEvents,
        ];
    }

    // ─── TAB: SOCIAL STRUCTURE ─────────────────────────────────────
    
    private function loadSocialData(World $world): array
    {
        return [
            'socialHistory' => $world->cosmicSnapshots()
                ->latest('year')
                ->limit(20)
                ->get()
                ->map(fn($s) => [
                    'year' => $s->year,
                    'classes' => $s->social_classes ?? []
                ])
        ];
    }

    private function getLatestSocialClasses(World $world): array
    {
        $snapshot = $world->cosmicSnapshots()->latest('year')->first();
        return $snapshot->social_classes ?? [];
    }

    // ─── TAB: HEROES ───────────────────────────────────────────────

    private function loadHeroesData(World $world): array
    {
        // Only valid for Vietnamese origin worlds
        if ($world->origin_type !== 'vietnamese') {
            return ['activeHeroes' => collect(), 'isVietnamese' => false];
        }

        $currentEra = (int) floor(($world->current_time ?? 0) / 50);
        
        $activeHeroes = \App\Domains\Vietnamese\Models\VietnameseHero::where('era', $currentEra)
            ->orderByDesc('impact_score')
            ->get();
            
        // Check previous era heroes for history (limit 5)
        $pastHeroes = \App\Domains\Vietnamese\Models\VietnameseHero::where('era', '<', $currentEra)
            ->orderByDesc('impact_score')
            ->limit(5)
            ->get();

        // Load Active Realm Contacts
        $realmContacts = app(\App\Domains\Vietnamese\Services\RealmContactService::class)
            ->getActiveContacts($currentEra);

        // Load Realm Influence Modifiers
        $realmModifiers = app(\App\Domains\Vietnamese\Services\RealmContactService::class)
            ->calculateRealmInfluence($world);

        return [
            'activeHeroes' => $activeHeroes,
            'pastHeroes' => $pastHeroes,
            'realmContacts' => $realmContacts,
            'realmModifiers' => $realmModifiers,
            'isVietnamese' => true,
            'currentEra' => $currentEra
        ];
    }

    // ─── CONTROL ACTIONS ─────────────────────────────────────────────

    public function injectEvent(string $worldId, Request $request)
    {
        $data = $request->validate([
            'event_type' => 'required|string',
            'description' => 'required|string',
            'magnitude' => 'required|numeric|min:0|max:1',
            'permanence' => 'required|numeric|min:0|max:1',
            'visibility' => 'required|string',
        ]);

        $data['world_id'] = $worldId;
        $result = $this->eventGate->processEvent($worldId, $data);

        if ($result['allowed']) {
            return back()->with('success', "Event Injected: " . $result['action']);
        }
        return back()->with('error', "Event Blocked: " . $result['reason']);
    }

    public function createScar(string $worldId, Request $request)
    {
        $data = $request->validate([
            'location_scope' => 'required|string',
            'constraint_rule' => 'required|string',
            'severity' => 'required|numeric|min:0|max:1',
        ]);

        $data['world_id'] = $worldId;
        Scar::create($data);

        return back()->with('success', 'Divine Scar branded upon the world.');
    }

    public function freeze(string $worldId)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $this->epochControl->freeze($world);
        return back()->with('success', 'World frozen.');
    }

    public function resume(string $worldId)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $this->epochControl->resume($world);
        return back()->with('success', 'World resumed.');
    }

    public function step(string $worldId)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $result = $this->epochControl->stepEpoch($world);
        return back()->with('success', 'Stepped 1 epoch. New year: ' . ($result['epoch'] ?? '?'));
    }

    public function rollback(string $worldId)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $snapshot = $this->epochControl->rollback($world);
        return back()->with('success', 'Rolled back to year ' . ($snapshot->year ?? '?'));
    }

    public function emergency(string $worldId, string $action, Request $request)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $snapshot = $world->cosmicSnapshots()->latest('year')->first();

        if (!$snapshot) {
            return back()->with('error', 'No cosmic snapshot found.');
        }

        $result = match ($action) {
            'entropy-shock' => $this->emergencyService->entropyShock(
                $snapshot, $request->input('magnitude', 0.15)
            ),
            'reduce-rigidity' => $this->emergencyService->reduceRigidity(
                $snapshot, $request->input('reduction', 0.1)
            ),
            'force-collapse' => $this->emergencyService->forceCollapse($snapshot),
            'toggle-emergent' => $this->emergencyService->toggleEmergent(
                $snapshot, (bool) $request->input('disabled', false)
            ),
            default => ['success' => false, 'error' => 'Unknown action'],
        };

        if ($result['success'] ?? false) {
            return back()->with('success', "Emergency '{$action}' executed.");
        }
        return back()->with('error', $result['error'] ?? 'Emergency action failed.');
    }

    /**
     * Frontend Trigger: Toggle Autonomous Mode
     */
    public function toggleAutonomous(string $worldId)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $world->autonomous = !$world->autonomous;
        $world->save();

        $status = $world->autonomous ? 'ENABLED' : 'DISABLED';
        return back()->with('success', "Autonomous Mode {$status} for {$world->name}.");
    }

    /**
     * Frontend Trigger: Manual Tick
     */
    public function autonomousTick(string $worldId)
    {
        \Illuminate\Support\Facades\Artisan::call('autonomous:tick');

        return back()->with('success', "Manual Tick Triggered.");
    }

    // ─── HELPERS ─────────────────────────────────────────────────────

    private function getLatestSnapshot(World $world)
    {
        return $world->cosmicSnapshots()->latest('year')->first();
    }

    private function calculateDistance($cosmic, $attractor): float
    {
        $dEntropy = ($cosmic->entropy ?? 0) - ($attractor->equilibriumEntropy ?? 0.2);
        $dStability = ($cosmic->stability ?? 0) - 0.7; // Proxy for attractor stability center
        $dEnergy = ($cosmic->energy ?? 0) - ($attractor->equilibriumEnergy ?? 0.5);

        return round(sqrt($dEntropy ** 2 + $dStability ** 2 + $dEnergy ** 2), 4);
    }

    /**
     * Get attractor memory data (morphing status, semantic info).
     */
    private function getAttractorMemoryData($snapshot): array
    {
        $cosmic = $snapshot->cosmic;
        
        return [
            'code' => $cosmic->currentAttractor ?? 'EQUILIBRIUM',
            'incarnation_id' => $cosmic->currentIncarnationId ?? null,
            'is_morphing' => $cosmic->morphTargetCentroid !== null,
            'morph_progress' => $this->calculateMorphProgress($cosmic),
            'morph_target' => $cosmic->morphTargetCentroid,
        ];
    }

    /**
     * Calculate morphing progress percentage.
     */
    private function calculateMorphProgress($cosmic): ?float
    {
        if (!$cosmic->morphTargetCentroid || !$cosmic->morphStartTick) {
            return null;
        }

        $elapsed = ($cosmic->year ?? 0) - $cosmic->morphStartTick;
        $typicalDuration = 100; // ticks

        return min(100, ($elapsed / $typicalDuration) * 100);
    }

    /**
     * Get incarnation tree for a specific attractor.
     */
    private function getIncarnationTree(?string $attractorCode): array
    {
        if (!$attractorCode || !$this->attractorRepo) {
            return [];
        }

        try {
            $attractor = $this->attractorRepo->findByCode($attractorCode);

            if (!$attractor) {
                return [];
            }

            return collect($attractor->getIncarnationTree())
                ->map(fn($inc) => [
                    'id' => $inc->id,
                    'parent_id' => $inc->parentIncarnationId,
                    'start_tick' => $inc->startTick,
                    'end_tick' => $inc->endTick,
                    'is_active' => $inc->isActive(),
                    'semantic' => $inc->semanticSnapshot,
                    'centroid' => $inc->centroidSnapshot,
                ])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}
