<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Cosmology\Services\ArtifactService;
use App\Models\Artifact;
use App\Models\UniverseModel;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    private ArtifactService $artifactService;

    public function __construct(ArtifactService $artifactService)
    {
        $this->artifactService = $artifactService;
    }

    /**
     * List all artifacts available in the bazaar.
     */
    public function index()
    {
        return response()->json($this->artifactService->getAvailableArtifacts());
    }

    /**
     * Infuse an artifact into a universe.
     */
    public function infuse(Request $request, $id)
    {
        $data = $request->validate([
            'target_universe_id' => 'required|string|exists:universes,id'
        ]);

        $artifact = Artifact::findOrFail($id);
        $target = UniverseModel::findOrFail($data['target_universe_id']);

        if ($this->artifactService->infuse($artifact, $target)) {
            return response()->json([
                'message' => "Artifact successfully infused into {$target->name}.",
                'artifact' => $artifact,
                'target_state' => $target->state_vector
            ]);
        }

        return response()->json(['error' => 'Artifact cannot be infused.'], 400);
    }
}
