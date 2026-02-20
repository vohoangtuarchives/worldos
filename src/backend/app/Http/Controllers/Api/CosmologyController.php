<?php

namespace App\Http\Controllers\Api;

use App\Domains\Cosmology\DataTransferObjects\VisualStateDTO;
use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Narrative\Services\LLMChronicler;
use App\Domains\Narrative\Services\NarrativeBridge;
use App\Domains\Runtime\Events\UniverseCollapsed;
use App\Domains\Runtime\Events\UniverseForked;
use App\Domains\Runtime\UniverseRuntimeService;
use App\Http\Controllers\Controller;
use App\Models\UniverseModel;
use App\Models\PlayerFaction;
use Tuzy\Domain\Runtime\Exception\UniverseNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\Cosmology\Agents\LegendaryAgent;
use App\Domains\Cosmology\Services\LifecycleService;
use Illuminate\Http\Request;

class CosmologyController extends Controller
{
    private CosmologyRepository $repository;
    private \App\Domains\Cosmology\Services\CouplingService $couplingService;
    private \App\Domains\Cosmology\Services\InterventionService $interventionService;
    private UniverseRuntimeService $runtimeService;

    public function __construct(
        CosmologyRepository $repository,
        \App\Domains\Cosmology\Services\CouplingService $couplingService,
        \App\Domains\Cosmology\Services\InterventionService $interventionService,
        UniverseRuntimeService $runtimeService
    ) {
        $this->repository = $repository;
        $this->couplingService = $couplingService;
        $this->interventionService = $interventionService;
        $this->runtimeService = $runtimeService;
    }

    public function index()
    {
        $universes = \App\Models\UniverseModel::with('cosmicFaction')->orderBy('updated_at', 'desc')->get();
        return $universes->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'age' => $u->age,
                'vector_summary' => [
                    'order' => $u->state_vector['order'] ?? 0,
                    'entropy' => $u->state_vector['entropy'] ?? 0,
                ],
                'coords' => $u->coords,
                'parameters' => $u->parameters,
                'is_archived' => $u->is_archived,
                'death_cause' => $u->death_cause,
                'created_at' => $u->created_at,
                'faction' => $u->cosmicFaction ? [
                    'id' => $u->cosmicFaction->id,
                    'name' => $u->cosmicFaction->name,
                    'color' => $u->cosmicFaction->color,
                    'ideology' => $u->cosmicFaction->ideology
                ] : null,
            ];
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'archetype' => 'nullable|string|in:BALANCED,UTOPIAN,DYSTOPIAN,CHAOTIC,VOID_TOUCHED',
            'faction_id' => 'nullable|exists:cosmic_factions,id',
            'world_id' => 'required|exists:worlds,id',
        ]);

        $id = (string) \Illuminate\Support\Str::uuid();
        $universe = $this->repository->createCustom($id, $data);

        return $this->formatResponse($universe);
    }

    /**
     * Analysis vector (coherence, entropy, belief_mass, resource_flow, stability, innovation_rate, contradiction_index)
     * for external analysis / AI. GET /api/cosmology/universe/{id}/analysis
     */
    public function analysis(string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        try {
            $universe = $this->repository->findOrSeed($id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage(), 'id' => $id], 404);
        }
        $pressureField = app(\App\Domains\Cosmology\Mathematics\PressureAccumulationField::class);
        $snapshot = $pressureField->getAnalysisSnapshot($universe->getState());
        $assessment = app(\App\Domains\Cosmology\Mathematics\CriticalityDetector::class)->assess($universe->getState());
        return response()->json([
            'universe_id' => $id,
            'age' => $universe->getAge(),
            'analysis_vector' => $snapshot,
            'phase' => $assessment['phase'],
            'zone' => $assessment['zone'] ?? null,
            'should_collapse' => $assessment['should_collapse'],
            'can_reorganize' => $assessment['can_reorganize'],
            'pressure' => $assessment['pressure'],
        ]);
    }

    public function show(string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        if (strlen($id) === 36 && str_contains($id, '-')) {
            $id = strtolower($id);
        }
        try {
            $universe = $this->repository->findOrSeed($id);
            return response()->json($this->formatResponse($universe));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage(), 'id' => $id], 404);
        } catch (\Throwable $e) {
            Log::error('CosmologyController::show failed', ['id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage(), 'id' => $id], 500);
        }
    }

    /**
     * On-demand narrative generation. Generates and caches chronicle; frontend calls when user opens Chronicle/Narrative section.
     * GET /api/cosmology/universe/{id}/chronicle
     */
    public function chronicle(string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        try {
            $universe = $this->repository->findOrSeed($id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage(), 'id' => $id], 404);
        }
        $cacheKey = 'universe_narrative_' . $universe->getId() . '_' . $universe->getAge();
        $narrative = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($universe) {
            $chronicler = app(LLMChronicler::class);
            return $chronicler->chronicle($universe);
        });
        return response()->json(['narrative' => $narrative]);
    }

    public function tick(string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        try {
            $universe = $this->runtimeService->tick($id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage(), 'id' => $id], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        // Coupling Interaction (Phase 10) for standalone universes
        $neighborModels = \App\Models\UniverseModel::where('id', '!=', $id)
            ->inRandomOrder()
            ->limit(5)
            ->get();
        $neighbors = [];
        foreach ($neighborModels as $nm) {
            $neighbors[] = $this->repository->find($nm->id);
        }
        $couplingResult = $neighbors ? $this->couplingService->interact($universe, $neighbors, 0.1) : null;
        $hasCoupling = false;
        if ($couplingResult) {
            $universe = new Universe($couplingResult, $universe->getHistory(), $universe->getId(), $universe->getAge());
            $hasCoupling = true;
            $this->repository->save($universe);
        }

        return $this->formatResponse($universe, true, $hasCoupling);
    }

    /**
     * Dispatch a job to advance the universe by N ticks (Phase 3.3). Lock per universe.
     * POST /api/cosmology/universe/{id}/advance with body { "ticks": N } (default 1).
     */
    public function advance(Request $request, string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        $universe = $this->repository->find($id);
        if ($universe === null) {
            throw UniverseNotFoundException::withId($id);
        }
        $ticks = (int) $request->input('ticks', 1);
        $ticks = max(1, min(100, $ticks));

        \App\Jobs\TickUniverseJob::dispatch($id, $ticks);

        return response()->json([
            'success' => true,
            'message' => "Advance job dispatched for {$ticks} tick(s).",
            'universe_id' => $id,
            'ticks' => $ticks,
        ]);
    }

    public function defyFate(Request $request, string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        try {
            $universe = $this->repository->findOrSeed($id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage(), 'id' => $id], 404);
        }
        $intention = $request->input('intention', 'RESTORE_ORDER');
        
        // Spawn a temporary Legendary Agent to perform the miracle
        // In a real persistence layer, we would load the authenticated user's agent
        $agent = new LegendaryAgent('Anonymous Hero', LegendaryAgent::ARCHETYPE_PROPHET);
        
        // Defy Fate
        $newState = $agent->defyFate($universe->getState(), $intention);
        
        // Update Universe
        $universe = new Universe($newState, $universe->getHistory(), $universe->getId(), $universe->getAge());
        
        $this->repository->save($universe);
        
        return $this->formatResponse($universe, true);
    }

    public function intervene(Request $request, string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        try {
            $universe = $this->repository->findOrSeed($id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage(), 'id' => $id], 404);
        }
        $type = $request->input('type', 'STABILIZE');
        // Strength could be dynamic, but fixed for now
        $strength = 0.15; 
        
        $newUniverse = $this->interventionService->intervene($universe, $type, $strength);
        
        $this->repository->save($newUniverse);
        
        return $this->formatResponse($newUniverse, true);
    }

    public function halt(string $id, LifecycleService $lifecycleService)
    {
        $repo = app(CosmologyRepository::class);
        $universe = $repo->find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }

        $model = UniverseModel::find($id);
        $worldId = $model?->world_id ? (string) $model->world_id : null;
        $state = $universe->getState();

        $lifecycleService->archive($universe, 'MANUAL_HALT');

        UniverseCollapsed::dispatch($id, $worldId, 'MANUAL_HALT', [
            'order' => $state->getOrder(),
            'entropy' => $state->getEntropy(),
        ]);

        return response()->json([
            'message' => 'Reality Halted by Architect Intervention',
            'id' => $id,
            'is_archived' => true
        ]);
    }

    public function fork(string $id, \App\Domains\Cosmology\Services\BifurcationService $bifurcationService)
    {
        $repo = app(CosmologyRepository::class);
        $universe = $repo->find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }

        $model = UniverseModel::find($id);
        $worldId = $model?->world_id ? (string) $model->world_id : null;

        $branches = $bifurcationService->split($universe);

        foreach ($branches as $branch) {
            UniverseForked::dispatch($id, $branch->getId(), $worldId, ['age' => $branch->getAge()]);
        }

        return response()->json([
            'message' => 'Timeline forked into parallel branches',
            'branches' => array_map(fn($b) => $b->getId(), $branches)
        ]);
    }

    public function getGlobalMeta()
    {
        $meta = \App\Models\MultiverseMeta::first();
        return response()->json($meta);
    }

    public function contributeToShield(Request $request, \App\Domains\Cosmology\Services\GlobalDefenseService $defenseService)
    {
        $data = $request->validate([
            'universe_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01'
        ]);

        $success = $defenseService->contribute($data['universe_id'], $data['amount']);
        if ($success === null) {
            throw UniverseNotFoundException::withId($data['universe_id']);
        }
        if ($success === false) return response()->json(['error' => 'Insufficient Resources'], 400);

        return response()->json([
            'message' => 'Contribution successful. Shield level increased.',
            'meta' => \App\Models\MultiverseMeta::first()
        ]);
    }

    public function updateLaws(Request $request, string $id)
    {
        $repo = app(CosmologyRepository::class);
        $universe = $repo->find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }

        $laws = $request->validate([
            'order' => 'nullable|numeric|between:0,1',
            'entropy' => 'nullable|numeric|between:0,1',
            'cohesion' => 'nullable|numeric|between:0,1',
            'innovation' => 'nullable|numeric|between:0,1',
            'milestones' => 'nullable|array'
        ]);

        $state = $universe->getState();
        $newVector = \App\Domains\Cosmology\Entities\WorldStateVector::create(
            $laws['order'] ?? $state->getOrder(),
            $laws['entropy'] ?? $state->getEntropy(),
            $laws['cohesion'] ?? $state->getCohesion(),
            $state->getLegitimacy(),
            $laws['innovation'] ?? $state->getInnovation(),
            $state->getMilitary(),
            $state->getInequality(),
            $state->getTrauma(),
            $state->getEliteCohesion(),
            $state->getResourceStock()
        );

        $params = $universe->getParameters();
        if (isset($laws['milestones'])) {
            $params['milestones'] = array_merge($params['milestones'] ?? [], $laws['milestones']);
        }

        $newUniverse = new \App\Domains\Cosmology\Entities\Universe(
            $newVector,
            $params,
            $universe->getId(),
            $universe->getAge(),
            $universe->getCoords()
        );

        $repo->save($newUniverse);

        return response()->json($this->formatResponse($newUniverse));
    }

    public function updateFactionEdict(Request $request, int $factionId)
    {
        $faction = \App\Models\CosmicFaction::find($factionId);
        if (!$faction) return response()->json(['error' => 'Faction Not Found'], 404);

        $data = $request->validate([
            'edict' => 'nullable|string|in:TOTALITARIAN_STABILITY,UNRESTRICTED_RESEARCH,GALACTIC_MOBILIZATION,NONE'
        ]);

        $stats = $faction->stats;
        $stats['active_edict'] = ($data['edict'] === 'NONE') ? null : $data['edict'];
        $faction->stats = $stats;
        $faction->save();

        return response()->json([
            'message' => 'Faction Edict Updated',
            'faction' => $faction->name,
            'active_edict' => $stats['active_edict']
        ]);
    }

    public function summonAgent(Request $request, string $id, \App\Domains\Cosmology\Services\AgentSpawnService $spawnService)
    {
        // ... previous code ...
        // (Keeping existing code, just anchoring)
        $universe = $this->repository->find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }

        $heroId = $request->input('hero_id');
        
        try {
            if ($heroId) {
                $agent = $spawnService->summonHero($universe, $heroId);
            } else {
                $agent = $spawnService->attemptSpawn($universe);
            }

            if (!$agent) {
                return response()->json(['message' => 'No suitable hero answered the call.'], 400);
            }

            return response()->json([
                'message' => 'Legendary Agent Summoned',
                'agent' => [
                    'name' => $agent->name,
                    'archetype' => $agent->archetype,
                    'quote' => $agent->quote
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Build a new Fleet.
     */
    public function buildFleet(Request $request, string $id, \App\Domains\Cosmology\Services\GalacticWarfareService $warfareService)
    {
        $universe = $this->repository->find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }

        $name = $request->input('name', 'Expeditionary Force ' . rand(100, 999));
        $cost = $request->input('cost', 10.0);

        try {
            $fleet = $warfareService->buildFleet($universe, $name, $cost);
            return response()->json([
                'message' => 'Fleet construction initialized.',
                'fleet' => $fleet
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Mobilize a Fleet.
     */
    /**
     * Phase 4.2: Bind authenticated user to a faction in this universe (one player per universe).
     */
    public function assignPlayerFaction(Request $request, string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        $universe = $this->repository->find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }
        $userId = $request->user()?->id ?? $request->input('user_id');
        if ($userId === null) {
            return response()->json(['error' => 'Authentication required or provide user_id'], 401);
        }
        $data = $request->validate(['faction_id' => 'required|exists:cosmic_factions,id']);
        $binding = PlayerFaction::updateOrCreate(
            ['user_id' => $userId, 'universe_id' => $id],
            ['faction_id' => $data['faction_id']]
        );
        return response()->json([
            'message' => 'Player bound to faction',
            'player_faction' => [
                'user_id' => $binding->user_id,
                'universe_id' => $binding->universe_id,
                'faction_id' => $binding->faction_id,
            ],
        ]);
    }

    /**
     * Phase 4.2: Get current user's faction binding for this universe.
     */
    public function getPlayerFaction(Request $request, string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        $userId = $request->user()?->id ?? $request->input('user_id');
        if ($userId === null) {
            return response()->json(['error' => 'Authentication required or provide user_id'], 401);
        }
        $binding = PlayerFaction::where('user_id', $userId)->where('universe_id', $id)->with('faction')->first();
        if (!$binding) {
            return response()->json(['player_faction' => null]);
        }
        return response()->json([
            'player_faction' => [
                'user_id' => $binding->user_id,
                'universe_id' => $binding->universe_id,
                'faction_id' => $binding->faction_id,
                'faction' => $binding->faction ? [
                    'id' => $binding->faction->id,
                    'name' => $binding->faction->name,
                    'ideology' => $binding->faction->ideology,
                    'color' => $binding->faction->color,
                    'status' => $binding->faction->status,
                ] : null,
            ],
        ]);
    }

    /**
     * Phase 4.3: Projection API — timeline (pressure curve, stage bands, collapse markers, fork points).
     * Includes dominance_timeline, epochs, turning_points, meta_history_summary when available.
     */
    public function timeline(
        string $id,
        ?\App\Domains\Cosmology\Contracts\AttractorInfluenceSnapshotRepositoryInterface $influenceRepo = null,
        ?\App\Domains\Cosmology\Services\PhaseDetector $phaseDetector = null,
        ?\App\Domains\Cosmology\Services\TurningPointEngine $turningPointEngine = null,
        ?\App\Domains\Cosmology\Services\MetaHistoryBuilder $metaHistoryBuilder = null
    ) {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        $snapshots = DB::table('civilization_snapshots')
            ->where('universe_id', $id)
            ->orderBy('tick')
            ->get(['tick', 'stage', 'pressure', 'state_jsonb']);
        $cycles = DB::table('civilization_cycles')
            ->where('universe_id', $id)
            ->orderBy('cycle_number')
            ->get(['cycle_number', 'start_tick', 'end_tick', 'collapse_reason']);
        $model = UniverseModel::find($id);
        $forkAtTick = $model->parameters['fork_at_tick'] ?? null;
        $forkPoints = $forkAtTick !== null ? [$forkAtTick] : [];

        $payload = [
            'universe_id' => $id,
            'pressure_curve' => $snapshots->pluck('pressure')->map(fn ($p) => $p !== null ? (float) $p : null)->values(),
            'ticks' => $snapshots->pluck('tick')->values(),
            'stage_bands' => $snapshots->map(fn ($s) => ['tick' => $s->tick, 'stage' => $s->stage])->values(),
            'collapse_markers' => $cycles->map(fn ($c) => ['cycle' => $c->cycle_number, 'end_tick' => $c->end_tick, 'reason' => $c->collapse_reason])->values(),
            'fork_points' => $forkPoints,
        ];

        if ($influenceRepo !== null && $phaseDetector !== null && $turningPointEngine !== null && $metaHistoryBuilder !== null) {
            $ticks = $snapshots->pluck('tick')->values();
            $fromTick = $ticks->isEmpty() ? 0 : (int) $ticks->first();
            $toTick = $ticks->isEmpty() ? 0 : (int) $ticks->last();
            $dominanceTimeline = $influenceRepo->getRange($id, $fromTick, $toTick);
            $phases = $phaseDetector->detectPhases($id, $fromTick, $toTick);
            $turningPoints = $turningPointEngine->getByUniverse($id, $fromTick, $toTick);
            $meta = $metaHistoryBuilder->buildFromPhases($phases, array_map(fn ($tp) => [
                'tick' => $tp['tick'],
                'type' => $tp['type'],
                'payload' => $tp['payload'],
            ], $turningPoints));
            $payload['dominance_timeline'] = $dominanceTimeline;
            $payload['epochs'] = $meta['eras'];
            $payload['turning_points'] = $meta['turning_points'];
            $payload['meta_history_summary'] = implode('; ', array_column($meta['eras'], 'label'));
            if ($payload['meta_history_summary'] === '') {
                $payload['meta_history_summary'] = 'Chưa có kỷ nguyên được ghi nhận.';
            }
        }

        return response()->json($payload);
    }

    /**
     * Phase 4.3: Projection API — faction lineage (parent_faction_id, cycle_origin, cycles_survived).
     */
    public function factionLineage(string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        $model = UniverseModel::find($id);
        if (!$model || !$model->cosmic_faction_id) {
            return response()->json(['universe_id' => $id, 'lineage' => []]);
        }
        $faction = \App\Models\CosmicFaction::with('parentFaction')->find($model->cosmic_faction_id);
        $lineage = [];
        $current = $faction;
        while ($current) {
            $lineage[] = [
                'id' => $current->id,
                'name' => $current->name,
                'status' => $current->status,
                'cycle_origin' => $current->cycle_origin,
                'cycles_survived' => $current->cycles_survived,
            ];
            $current = $current->parentFaction;
        }
        return response()->json(['universe_id' => $id, 'lineage' => $lineage]);
    }

    /**
     * Phase 5: Generate narrative (arc + first chapter) from universe state and pressure curve.
     * POST body: world_state (optional), pressure_points (optional), arc_id (optional), async (optional bool).
     * If async=true, dispatches NarrativeGenerateJob and returns result_key; use GET .../narrative/result to fetch.
     */
    public function generateNarrative(Request $request, string $id, \App\Domains\Narrative\Planning\StoryOrchestrator $orchestrator)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        $universe = $this->repository->find($id);
        if (!$universe) {
            throw UniverseNotFoundException::withId($id);
        }

        $worldState = $request->input('world_state');
        $pressurePoints = $request->input('pressure_points');

        if ($worldState === null || $pressurePoints === null) {
            $state = $universe->getState();
            $params = $universe->getParameters();
            $worldState = array_merge($state->getAll(), [
                'structural_anchor' => $params['structural_anchor'] ?? 'academic_system',
            ]);
            $snapshots = DB::table('civilization_snapshots')
                ->where('universe_id', $id)
                ->orderBy('tick')
                ->get(['tick', 'pressure']);
            $pressurePoints = $snapshots->map(fn ($s) => [
                'type' => 'pressure',
                'value' => $s->pressure !== null ? (float) $s->pressure : 0.5,
                'label' => 'tick ' . $s->tick,
            ])->values()->all();
            if (empty($pressurePoints)) {
                $pressurePoints = [['type' => 'pressure', 'value' => 0.5, 'label' => 'default']];
            }
        }

        $worldState = is_array($worldState) ? $worldState : [];
        $pressurePoints = is_array($pressurePoints) ? $pressurePoints : [];
        $arcId = $request->input('arc_id');
        $arcId = is_string($arcId) ? $arcId : null;
        $useMetaHistory = $request->boolean('use_meta_history');

        if ($useMetaHistory && $worldState !== [] && app()->bound(\App\Domains\Cosmology\Services\MetaHistoryBuilder::class)) {
            $metaBuilder = app(\App\Domains\Cosmology\Services\MetaHistoryBuilder::class);
            $metaSummary = $metaBuilder->buildSummaryString($id);
            $worldState['meta_history_summary'] = $metaSummary;
        }

        if ($request->boolean('async')) {
            \App\Jobs\NarrativeGenerateJob::dispatch($id, $worldState, $pressurePoints, $arcId);
            return response()->json([
                'success' => true,
                'message' => 'Narrative generation job dispatched.',
                'universe_id' => $id,
                'result_key' => 'narrative_result:' . $id,
            ]);
        }

        $result = $orchestrator->generateArcAndFirstChapter($worldState, $pressurePoints, $arcId);

        return response()->json([
            'success' => true,
            'universe_id' => $id,
            'arcs' => $result['arcs'],
            'blueprints' => $result['blueprints'],
            'draft' => $result['draft'],
            'score' => $result['score'],
            'refined' => $result['refined'],
        ]);
    }

    /**
     * Phase 5: Get cached narrative result after async generation (NarrativeGenerateJob).
     */
    public function narrativeResult(string $id)
    {
        $id = ($id === 'latest' || $id === 'demo') ? 'demo-universe' : $id;
        $result = \Illuminate\Support\Facades\Cache::get('narrative_result:' . $id);
        if ($result === null) {
            return response()->json(['error' => 'No result found. Run generate with async=true first.', 'universe_id' => $id], 404);
        }
        return response()->json(array_merge(['success' => true, 'universe_id' => $id], $result));
    }

    public function getActiveFleets()
    {
        $fleets = \App\Models\Fleet::where('status', 'MOVING')
            ->orWhere('status', 'ENGAGING')
            ->get()
            ->map(function($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'universe_id' => $f->universe_id,
                    'destination_id' => $f->destination_universe_id,
                    'status' => $f->status,
                    'faction_id' => $f->faction_id,
                    'type' => $f->type
                ];
            });
            
        return response()->json($fleets);
    }

    public function mobilizeFleet(Request $request, string $id, string $fleetId, \App\Domains\Cosmology\Services\GalacticWarfareService $warfareService)
    {
        $fleet = \App\Models\Fleet::find($fleetId);
        if (!$fleet) return response()->json(['error' => 'Fleet Not Found'], 404);

        $destinationId = $request->input('destination_id');
        if (!$destinationId) return response()->json(['error' => 'Destination required'], 400);

        try {
            $fleet = $warfareService->mobilizeFleet($fleet, $destinationId);
            return response()->json([
                'message' => 'Fleet immobilized. Hyperspace coordinates locked.',
                'fleet' => $fleet
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function formatResponse(Universe $universe, bool $ticked = false, bool $couplingEvent = false): array
    {
        // Performance: narrative is on-demand. Return cached narrative if present (from GET .../chronicle), else null.
        $cacheKey = 'universe_narrative_' . $universe->getId() . '_' . $universe->getAge();
        $narrative = \Illuminate\Support\Facades\Cache::get($cacheKey);

        // Check for active anomalies (Phase 18)
        $anomalyService = app(\App\Domains\Cosmology\Services\AnomalyService::class);
        $activeAnomalies = $anomalyService->getActiveAnomalies($universe);
        
        // Load Fleets (Phase 33)
        // Note: Using the Model directly here since Repository returns Entity without Fleets property
        // Ideally we should move this to Repository/Entity, but for speed in this phase:
        $fleets = \App\Models\Fleet::where('universe_id', $universe->getId())->get()->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'strength' => $f->strength,
                'status' => $f->status,
                'owner' => $f->faction_id ? 'Faction' : 'Independent',
                'type' => $f->type,
                'coordinates' => $f->coordinates,
                'destination_id' => $f->destination_universe_id
            ];
        });

        $s = $universe->getState(); // Define $s here as it's used below

        return [
            'id' => $universe->getId(),
            'name' => $universe->getParameters()['name'] ?? 'Unknown Universe',
            'vector' => [
                'order' => $s->getOrder(),
                'entropy' => $s->getEntropy(),
                'cohesion' => $s->getCohesion(),
                'legitimacy' => $s->getLegitimacy(),
                'innovation' => $s->getInnovation(),
                'military' => $s->getMilitary(),
                'inequality' => $s->getInequality(),
                'trauma' => $s->getTrauma(),
                'elite_cohesion' => $s->getEliteCohesion(),
                'resource_stock' => $s->getResourceStock(),
            ],
            'parameters' => array_merge($universe->getParameters(), [
                'milestones' => $universe->getHistory(), // If history is stored here
                'active_agent' => $universe->getParameters()['active_agent'] ?? null,
                'anomalies' => $activeAnomalies,
                'fleets' => $fleets
            ]),
            'narrative' => $narrative,
            'age' => $universe->getAge(),
            'coords' => $universe->getCoords(),
            'ticked' => $ticked,
            'coupling_event' => $couplingEvent,
            'faction' => $universe->getCosmicFactionId() ? (function($id) {
                $f = \App\Models\CosmicFaction::find($id);
                return $f ? [
                    'id' => $f->id,
                    'name' => $f->name,
                    'color' => $f->color,
                    'ideology' => $f->ideology
                ] : null;
            })($universe->getCosmicFactionId()) : null,
        ];
    }
}
