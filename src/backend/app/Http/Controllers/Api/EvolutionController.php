<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evolution\Experiment;
use App\Models\Evolution\Generation;
use App\Models\UniverseModel;
use Illuminate\Http\Request;
use WorldOS\Simulation\Application\AdvanceTick\AdvanceTickCommand;
use WorldOS\Simulation\Application\AdvanceTick\AdvanceTickHandler;
use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use Illuminate\Support\Facades\DB;

class EvolutionController extends Controller
{
    public function listExperiments()
    {
        $experiments = Experiment::orderBy('created_at', 'desc')->get();
        return response()->json($experiments);
    }

    public function createExperiment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $experiment = Experiment::create([
            'name' => $validated['name'],
            'status' => 'PENDING',
        ]);

        return response()->json($experiment, 201);
    }

    public function listGenerations($experimentId)
    {
        $generations = Generation::where('experiment_id', $experimentId)
            ->orderBy('generation_index', 'asc')
            ->get();
            
        return response()->json($generations);
    }

    public function listUniverses($generationId)
    {
        $universes = UniverseModel::where('generation_id', $generationId)->get();
        return response()->json($universes);
    }

    public function getLineage($universeId)
    {
        // Recursively build lineage tree for a root universe
        $root = UniverseModel::find($universeId);
        
        if (!$root) {
            return response()->json(['message' => 'Universe not found'], 404);
        }

        $tree = $this->buildLineageTree($root);
        return response()->json($tree);
    }

    private function buildLineageTree(UniverseModel $universe)
    {
        $children = UniverseModel::where('parent_universe_id', $universe->id)->get();
        
        $childrenData = [];
        foreach ($children as $child) {
            $childrenData[] = $this->buildLineageTree($child);
        }

        return [
            'id' => $universe->id,
            'name' => $universe->name,
            'status' => $universe->status,
            'lifespan' => $universe->lifespan,
            'fitness' => $universe->fitness_total_score,
            'generation_id' => $universe->generation_id,
            'children' => $childrenData,
        ];
    }

    public function getUniverse($id)
    {
        $universe = UniverseModel::find($id);
        
        if (!$universe) {
            return response()->json(['message' => 'Universe not found'], 404);
        }

        return response()->json($universe);
    }

    public function simulateUniverse(Request $request, $id, AdvanceTickHandler $handler)
    {
        $universe = UniverseModel::find($id);
        if (!$universe) {
            return response()->json(['message' => 'Universe not found'], 404);
        }

        $ticks = $request->input('ticks', 1);
        $seed = $request->input('seed', null);

        $results = [];
        
        DB::beginTransaction();
        try {
            for ($i = 0; $i < $ticks; $i++) {
                $command = new AdvanceTickCommand(
                    universeId: $id,
                    seed: $seed ? (int)$seed + $i : random_int(1, 99999999)
                );
                
                $result = $handler->handle($command);
                $results[] = [
                    'tick' => $result->tickResult->tick,
                    'events_count' => count($result->chronicleEvents)
                ];
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Simulation failed', 'error' => $e->getMessage()], 500);
        }

        $updatedUniverse = UniverseModel::find($id);

        return response()->json([
            'message' => "Simulated $ticks ticks successfully",
            'universe' => $updatedUniverse,
            'ticks_processed' => count($results)
        ]);
    }

    public function getChronicles($id)
    {
        // Sử dụng Query Builder hoặc Model tuỳ thuộc vào cách lưu trữ ChronicleEvent.
        // Giả sử ChronicleEvent được lưu trong bảng chronicles. 
        // Trong hệ thống V5/V6, Chronicle được lưu qua DTO hoặc DocumentStore.
        // Ở đây lấy trực tiếp từ model nếu có hoặc mảng ChronicleRecordRepository
        
        $chronicles = DB::table('chronicle_events')
            ->where('universe_id', $id)
            ->orderBy('tick', 'desc')
            ->limit(100)
            ->get();

        return response()->json($chronicles);
    }
}
