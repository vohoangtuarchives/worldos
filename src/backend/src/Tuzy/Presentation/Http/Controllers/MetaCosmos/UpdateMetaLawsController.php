<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\MetaCosmos;

use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateMetaLawsController extends Controller
{
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $world = World::findOrFail($id);

        $laws = $request->only([
            'min_entropy',
            'max_entropy',
            'base_mutation_rate',
            'interaction_gain'
        ]);

        $config = $world->config ?? [];
        $config['law_genome'] = array_merge($config['law_genome'] ?? [], $laws);
        
        $world->update(['config' => $config]);

        return response()->json([
            'status' => 'success',
            'data' => $world->config['law_genome']
        ]);
    }
}
