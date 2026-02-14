<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\World\Actions\TickWorldAction;
use App\Domains\World\Services\WorldLifecycleAnalyzer;
use App\Domains\Intelligence\Services\WorldIntelligenceService;
use App\Domains\Material\Services\WorldMaterialTracker;
use App\Domains\World\Repositories\WorldRepository;
use App\Domains\Character\Repositories\CharacterSurvivalRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class WorldController extends Controller
{
    public function __construct(
        private readonly WorldRepository $worldRepository,
        private readonly TickWorldAction $tickAction,
        private readonly WorldLifecycleAnalyzer $lifecycleAnalyzer,
        private readonly WorldIntelligenceService $intelligenceService,
        private readonly WorldMaterialTracker $materialTracker,
        private readonly CharacterSurvivalRepository $characterRepository,
    ) {}

    public function index(): View
    {
        $worlds = $this->worldRepository->findAll();
        
        return view('worlds.index', [
            'worlds' => $worlds,
            'totalWorlds' => $worlds->count(),
            'autonomousWorlds' => $worlds->filter(fn($w) => $w->isAutonomous())->count(),
        ]);
    }

    public function show(string $worldId): View
    {
        $world = $this->worldRepository->findById($worldId);
        
        if (!$world) {
            abort(404, 'World not found');
        }

        $characters = $this->characterRepository->findByWorldId($worldId);
        $lifecycleReport = $this->lifecycleAnalyzer->analyzeLifecycle($world, collect($characters));
        $materials = $this->materialTracker->trackWorldMaterials($world);
        $intelligence = $this->intelligenceService->gatherIntelligence(
            $world, 
            collect($characters), 
            collect([])
        );

        return view('worlds.show', [
            'world' => $world,
            'characters' => $characters,
            'lifecycleReport' => $lifecycleReport,
            'materials' => $materials,
            'intelligence' => $intelligence,
        ]);
    }

    public function dashboard(string $worldId): View
    {
        $world = $this->worldRepository->findById($worldId);
        
        if (!$world) {
            abort(404, 'World not found');
        }

        return view('worlds.dashboard', [
            'world' => $world,
        ]);
    }

    public function tick(Request $request, string $worldId): JsonResponse
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                return response()->json(['error' => 'World not found'], 404);
            }

            $characters = $this->characterRepository->findByWorldId($worldId);
            $count = $request->get('count', 1);

            $results = [];
            for ($i = 0; $i < $count; $i++) {
                $result = $this->tickAction->execute($world, collect($characters));
                $results[] = $result->toArray();
                $world = $result->world; // Update world reference
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'world' => [
                    'id' => $world->id(),
                    'tick' => $world->currentTick(),
                    'entropy' => $world->currentEntropy()->value(),
                    'autonomous' => $world->isAutonomous(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function start(string $worldId): JsonResponse
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                return response()->json(['error' => 'World not found'], 404);
            }

            $world = $world->enableAutonomous();
            $this->worldRepository->save($world);

            return response()->json([
                'success' => true,
                'message' => "World {$worldId} started in autonomous mode",
                'world' => [
                    'id' => $world->id(),
                    'autonomous' => $world->isAutonomous(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stop(string $worldId): JsonResponse
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                return response()->json(['error' => 'World not found'], 404);
            }

            $world = $world->disableAutonomous();
            $this->worldRepository->save($world);

            return response()->json([
                'success' => true,
                'message' => "World {$worldId} stopped",
                'world' => [
                    'id' => $world->id(),
                    'autonomous' => $world->isAutonomous(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function status(string $worldId): JsonResponse
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                return response()->json(['error' => 'World not found'], 404);
            }

            $characters = $this->characterRepository->findByWorldId($worldId);
            $lifecycleReport = $this->lifecycleAnalyzer->analyzeLifecycle($world, collect($characters));
            $materials = $this->materialTracker->trackWorldMaterials($world);

            return response()->json([
                'success' => true,
                'world' => [
                    'id' => $world->id(),
                    'name' => $world->name(),
                    'tick' => $world->currentTick(),
                    'entropy' => $world->currentEntropy()->value(),
                    'autonomous' => $world->isAutonomous(),
                    'preset' => $world->preset(),
                    'last_tick_at' => $world->lastTickAt()?->format('Y-m-d H:i:s'),
                ],
                'characters' => [
                    'total' => count($characters),
                    'alive' => count(array_filter($characters, fn($c) => $c->isAlive())),
                    'dead' => count(array_filter($characters, fn($c) => !$c->isAlive())),
                ],
                'lifecycle' => $lifecycleReport->toArray(),
                'materials' => $materials->getSummary(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function intelligence(string $worldId): JsonResponse
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                return response()->json(['error' => 'World not found'], 404);
            }

            $characters = $this->characterRepository->findByWorldId($worldId);
            $intelligence = $this->intelligenceService->gatherIntelligence(
                $world, 
                collect($characters), 
                collect([])
            );

            return response()->json([
                'success' => true,
                'intelligence' => $intelligence->toArray(),
                'summary' => $intelligence->getSummary(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function materials(string $worldId): JsonResponse
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                return response()->json(['error' => 'World not found'], 404);
            }

            $materials = $this->materialTracker->trackWorldMaterials($world);
            $statistics = $this->materialTracker->getMaterialStatistics($world);

            return response()->json([
                'success' => true,
                'materials' => $materials->toArray(),
                'statistics' => $statistics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function realtime(string $worldId): JsonResponse
    {
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                return response()->json(['error' => 'World not found'], 404);
            }

            // Get real-time data
            $characters = $this->characterRepository->findByWorldId($worldId);
            $materials = $this->materialTracker->trackWorldMaterials($world);
            $intelligence = $this->intelligenceService->gatherIntelligence(
                $world, 
                collect($characters), 
                collect([])
            );

            return response()->json([
                'success' => true,
                'timestamp' => now()->toISOString(),
                'world' => [
                    'id' => $world->id(),
                    'tick' => $world->currentTick(),
                    'entropy' => $world->currentEntropy()->value(),
                    'autonomous' => $world->isAutonomous(),
                ],
                'characters' => [
                    'total' => count($characters),
                    'alive' => count(array_filter($characters, fn($c) => $c->isAlive())),
                    'dead' => count(array_filter($characters, fn($c) => !$c->isAlive())),
                ],
                'materials' => [
                    'total' => $materials->count(),
                    'active' => $materials->getActive()->count(),
                    'broken' => $materials->getBroken()->count(),
                    'average_durability' => $materials->getAverageDurability(),
                ],
                'intelligence' => [
                    'total_reports' => $intelligence->count(),
                    'reliable_reports' => $intelligence->getReliable()->count(),
                    'high_urgency' => $intelligence->getHighUrgency()->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
