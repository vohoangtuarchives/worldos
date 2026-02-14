<?php

namespace App\Http\Controllers\Api\Reader;

use App\Http\Controllers\Controller;
use App\Domains\Reader\ReaderInteractionEngine;
use App\Domains\Reader\InteractionLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * ReaderInteractionController - API for reader choices and votes
 */
class ReaderInteractionController extends Controller
{
    public function __construct(
        private ReaderInteractionEngine $engine,
        private InteractionLogger $logger
    ) {}

    /**
     * Get available choices for an epoch.
     * 
     * GET /api/reader/worlds/{world}/choices/{epoch}
     */
    public function getChoices(int $worldId, int $epoch): JsonResponse
    {
        try {
            $choices = $this->engine->getChoices($worldId, $epoch);

            return response()->json([
                'world_id' => $worldId,
                'epoch' => $epoch,
                'choices' => $choices,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate choices',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit a vote for a choice.
     * 
     * POST /api/reader/worlds/{world}/vote
     * Body: { "epoch": 42, "choice_id": "...", "option_id": "..." }
     */
    public function vote(Request $request, int $worldId): JsonResponse
    {
        $validated = $request->validate([
            'epoch' => 'required|integer',
            'choice_id' => 'required|string',
            'option_id' => 'required|string',
        ]);

        try {
            // Get reader ID (if authenticated) or session
            $readerId = auth()->id();
            $session = $request->session()->getId();

            // Log vote
            $this->logger->logVote(
                $worldId,
                $validated['epoch'],
                $validated['choice_id'],
                $validated['option_id'],
                $readerId,
                $session
            );

            return response()->json([
                'success' => true,
                'message' => 'Vote recorded',
                'world_id' => $worldId,
                'epoch' => $validated['epoch'],
                'choice_id' => $validated['choice_id'],
                'option_id' => $validated['option_id'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to record vote',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit a reaction.
     * 
     * POST /api/reader/worlds/{world}/react
     * Body: { "epoch": 42, "reaction_type": "support" }
     */
    public function react(Request $request, int $worldId): JsonResponse
    {
        $validated = $request->validate([
            'epoch' => 'required|integer',
            'reaction_type' => 'required|in:support,oppose,sadness,anger,hope',
        ]);

        try {
            // Get reader ID (if authenticated) or session
            $readerId = auth()->id();
            $session = $request->session()->getId();

            // Log reaction
            $this->logger->logReaction(
                $worldId,
                $validated['epoch'],
                $validated['reaction_type'],
                $readerId,
                $session
            );

            return response()->json([
                'success' => true,
                'message' => 'Reaction recorded',
                'world_id' => $worldId,
                'epoch' => $validated['epoch'],
                'reaction_type' => $validated['reaction_type'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to record reaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get results for an epoch.
     * 
     * GET /api/reader/worlds/{world}/results/{epoch}
     */
    public function getResults(int $worldId, int $epoch): JsonResponse
    {
        try {
            $results = $this->logger->getResultsForEpoch($worldId, $epoch);

            return response()->json([
                'world_id' => $worldId,
                'epoch' => $epoch,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get results',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
