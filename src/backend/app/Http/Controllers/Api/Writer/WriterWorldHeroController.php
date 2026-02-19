<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Vietnamese\Models\WorldHero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WriterWorldHeroController extends Controller
{
    public function index(string $worldId): JsonResponse
    {
        $heroes = WorldHero::where('world_id', $worldId)
            ->orderBy('spawned_at_tick', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $heroes
        ]);
    }
}
