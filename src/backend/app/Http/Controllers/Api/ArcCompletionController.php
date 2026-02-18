<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\Conflict\StructuralInterpreter;
use App\Domains\Cosmology\Mathematics\StressModel;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Mutation\UniverseMutationService;
use App\Domains\Narrative\Planning\ArcSelector;
use App\Domains\Narrative\Planning\ArcType;
use App\Domains\Narrative\Planning\DefaultOutcome;
use App\Domains\Narrative\Planning\OutcomeRuleEngine;
use App\Domains\Narrative\Planning\StoryOutcomeDTO;
use App\Http\Controllers\Controller;
use App\Domains\Mutation\OutcomeQuantizer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Complete-arc API: preview, confirm, auto-resolve.
 * Mutation committed only via UniverseMutationService (single boundary).
 */
class ArcCompletionController extends Controller
{
    public function __construct(
        private readonly CosmologyRepository $cosmologyRepository,
        private readonly StructuralInterpreter $structuralInterpreter,
        private readonly ArcSelector $arcSelector,
        private readonly OutcomeRuleEngine $ruleEngine,
        private readonly OutcomeQuantizer $quantizer,
        private readonly UniverseMutationService $mutationService,
        private readonly StressModel $stressModel,
    ) {
    }

    /**
     * POST /arc/{id}/preview
     * Body: universe_id (required), user_override (win|lose|partial|null), override_intensity (0-1|null)
     * Returns: final_outcome (StoryOutcomeDTO), mutation_preview, phase_change
     */
    public function preview(Request $request, string $id): JsonResponse
    {
        $universeId = $request->input('universe_id');
        if (!is_string($universeId) || $universeId === '') {
            return response()->json(['error' => 'universe_id required'], 422);
        }

        $universe = $this->cosmologyRepository->find($universeId);
        if ($universe === null) {
            return response()->json(['error' => 'Universe not found'], 404);
        }

        $state = $universe->getState();
        $seeds = $this->structuralInterpreter->interpretFromState($state);
        $selected = $this->arcSelector->selectWithDominant($seeds);
        $arcType = $selected['arc_type'];
        $dominantSeed = $selected['dominant_seed'];

        if ($dominantSeed === null) {
            return response()->json([
                'final_outcome' => null,
                'mutation_preview' => [],
                'phase_change' => false,
                'message' => 'No conflict seeds; no default outcome.',
            ]);
        }

        $context = $this->contextFromState($state);
        $default = $this->ruleEngine->defaultOutcome(
            $arcType,
            $dominantSeed,
            $context,
            $id
        );

        $userOverride = $request->input('user_override');
        $overrideIntensity = $request->input('override_intensity');
        if ($overrideIntensity !== null) {
            $overrideIntensity = is_numeric($overrideIntensity) ? (float) $overrideIntensity : null;
        }

        $dto = $this->quantizer->quantize(
            $default,
            $userOverride,
            $overrideIntensity,
            false,
            $id
        );

        $preview = $this->mutationService->preview($universeId, $dto, $arcType);

        return response()->json([
            'final_outcome' => $dto->toArray(),
            'mutation_preview' => $preview['mutation_preview'],
            'phase_change' => $preview['phase_change'],
            'arc_type' => $arcType->value,
        ]);
    }

    /**
     * POST /arc/{id}/confirm
     * Body: universe_id (required), final_outcome (result, intensity, scope), arc_type (optional)
     * Commits full mutation via UniverseMutationService.
     */
    public function confirm(Request $request, string $id): JsonResponse
    {
        $universeId = $request->input('universe_id');
        if (!is_string($universeId) || $universeId === '') {
            return response()->json(['error' => 'universe_id required'], 422);
        }

        $outcomePayload = $request->input('final_outcome');
        if (!is_array($outcomePayload)) {
            return response()->json(['error' => 'final_outcome required (result, intensity, scope)'], 422);
        }

        $result = $outcomePayload['result'] ?? null;
        $intensity = isset($outcomePayload['intensity']) ? (float) $outcomePayload['intensity'] : null;
        $scope = $outcomePayload['scope'] ?? 'local';
        if (!in_array($result, [StoryOutcomeDTO::RESULT_WIN, StoryOutcomeDTO::RESULT_LOSE, StoryOutcomeDTO::RESULT_PARTIAL], true)) {
            return response()->json(['error' => 'final_outcome.result must be win, lose, or partial'], 422);
        }
        if ($intensity === null || $intensity < 0 || $intensity > 1) {
            return response()->json(['error' => 'final_outcome.intensity required in [0, 1]'], 422);
        }

        $dto = new StoryOutcomeDTO($result, $intensity, $scope, true, $id);
        $arcType = $this->arcTypeFromRequest($request);

        try {
            $this->mutationService->commit($universeId, $dto, $arcType);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Arc outcome committed (full mutation).',
            'final_outcome' => $dto->toArray(),
        ]);
    }

    /**
     * POST /arc/{id}/auto-resolve
     * Body: universe_id (required), final_outcome (optional; if omitted we compute default), arc_type (optional)
     * Commits shadow mutation (multiplier 0.3), no phase change.
     */
    public function autoResolve(Request $request, string $id): JsonResponse
    {
        $universeId = $request->input('universe_id');
        if (!is_string($universeId) || $universeId === '') {
            return response()->json(['error' => 'universe_id required'], 422);
        }

        $universe = $this->cosmologyRepository->find($universeId);
        if ($universe === null) {
            return response()->json(['error' => 'Universe not found'], 404);
        }

        $arcType = $this->arcTypeFromRequest($request);
        $outcomePayload = $request->input('final_outcome');
        if (is_array($outcomePayload)) {
            $result = $outcomePayload['result'] ?? StoryOutcomeDTO::RESULT_PARTIAL;
            $intensity = isset($outcomePayload['intensity']) ? (float) $outcomePayload['intensity'] : 0.3;
            $scope = $outcomePayload['scope'] ?? 'local';
        } else {
            $state = $universe->getState();
            $seeds = $this->structuralInterpreter->interpretFromState($state);
            $selected = $this->arcSelector->selectWithDominant($seeds);
            $arcType = $arcType ?? $selected['arc_type'];
            $dominantSeed = $selected['dominant_seed'];
            if ($dominantSeed === null) {
                return response()->json(['error' => 'No conflict seeds; cannot auto-resolve.'], 422);
            }
            $context = $this->contextFromState($state);
            $default = $this->ruleEngine->defaultOutcome($arcType, $dominantSeed, $context, $id);
            $result = $default->result;
            $intensity = $default->intensity;
            $scope = $default->scope;
        }

        $dto = new StoryOutcomeDTO($result, $intensity, $scope, false, $id);

        if ($arcType === null) {
            $state = $universe->getState();
            $seeds = $this->structuralInterpreter->interpretFromState($state);
            $selected = $this->arcSelector->selectWithDominant($seeds);
            $arcType = $selected['arc_type'];
        }

        try {
            $this->mutationService->commit($universeId, $dto, $arcType);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Arc auto-resolved (shadow mutation).',
            'final_outcome' => $dto->toArray(),
        ]);
    }

    private function contextFromState(\App\Domains\Cosmology\Entities\WorldStateVector $state): array
    {
        $components = $this->stressModel->getComponents($state);
        return [
            'influence' => ($state->getCohesion() + $state->getLegitimacy()) / 2.0,
            'cohesion' => $state->getCohesion(),
            'instability' => $state->getEntropy(),
            'alliance_weight' => $state->getEliteCohesion(),
        ];
    }

    private function arcTypeFromRequest(Request $request): ?ArcType
    {
        $value = $request->input('arc_type');
        if (!is_string($value)) {
            return null;
        }
        return ArcType::tryFrom($value);
    }
}
