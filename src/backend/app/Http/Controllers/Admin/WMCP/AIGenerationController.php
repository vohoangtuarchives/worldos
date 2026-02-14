<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIGenerationController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('ai_generations')
            ->orderBy('created_at', 'desc');

        // Filter by World
        if ($request->filled('world_id')) {
            $query->where('world_id', $request->world_id);
        }

        // Filter by Context Type
        if ($request->filled('context_type')) {
            $query->where('context_type', $request->context_type);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $generations = $query->paginate(30);
        
        // Get distinct context types for filter
        $contextTypes = DB::table('ai_generations')
            ->select('context_type')
            ->distinct()
            ->pluck('context_type');

        $worlds = DB::table('worlds')->select('id', 'name')->get();

        return view('admin.wmcp.ai-generations.index', compact('generations', 'contextTypes', 'worlds'));
    }

    public function show($id)
    {
        $generation = DB::table('ai_generations')->where('id', $id)->first();
        
        if (!$generation) {
            abort(404);
        }

        // Get extracted claims for this generation
        $claims = DB::table('ai_extracted_claims')
            ->where('generation_id', $id)
            ->get();

        return view('admin.wmcp.ai-generations.show', compact('generation', 'claims'));
    }
}
