<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Models\StyleProposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WriterGovernanceController extends Controller
{
    /**
     * GET /api/writer/governance/proposals/{worldId}
     */
    public function proposals(string $worldId): JsonResponse
    {
        $proposals = StyleProposal::where('world_id', $worldId)
            ->where('status', 'PENDING')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $proposals
        ]);
    }

    /**
     * POST /api/writer/governance/proposals/{proposalId}/approve
     */
    public function approve(string $proposalId): JsonResponse
    {
        $proposal = StyleProposal::findOrFail($proposalId);
        $proposal->status = 'APPROVED';
        $proposal->save();

        // TODO: Trigger the actual mutation on UniverseStyle
        
        return response()->json([
            'success' => true,
            'message' => 'Proposal approved.'
        ]);
    }

    /**
     * POST /api/writer/governance/proposals/{proposalId}/reject
     */
    public function reject(string $proposalId): JsonResponse
    {
        $proposal = StyleProposal::findOrFail($proposalId);
        $proposal->status = 'REJECTED';
        $proposal->save();

        return response()->json([
            'success' => true,
            'message' => 'Proposal rejected.'
        ]);
    }
}
