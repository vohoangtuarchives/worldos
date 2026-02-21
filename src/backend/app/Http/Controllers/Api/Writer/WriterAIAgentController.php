<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\WorldManagement\Repositories\AIGovernanceRepository;
use App\Models\AIFeatureAgentConfig;
use App\Models\AIProviderRequestHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WriterAIAgentController extends Controller
{
    public function __construct(
        protected AIGovernanceRepository $repository
    ) {}

    public function getMetrics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->repository->getGlobalMetrics(),
        ]);
    }

    public function getGenerations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->repository->getRecentGenerations(),
        ]);
    }

    public function getAgents(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->repository->getAgentStats(),
        ]);
    }

    public function getFeatureConfigs(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => AIFeatureAgentConfig::query()->orderBy('feature_key')->get(),
        ]);
    }

    public function upsertFeatureConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature_key' => 'required|string|max:120',
            'agent_name' => 'required|string|max:191',
            'provider' => 'required|string|max:100',
            'model' => 'nullable|string|max:191',
            'system_prompt' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'enabled' => 'nullable|boolean',
        ]);

        $config = AIFeatureAgentConfig::query()->updateOrCreate(
            ['feature_key' => $validated['feature_key']],
            [
                'agent_name' => $validated['agent_name'],
                'provider' => $validated['provider'],
                'model' => $validated['model'] ?? null,
                'system_prompt' => $validated['system_prompt'] ?? null,
                'enabled' => (bool) ($validated['enabled'] ?? false),
                'options' => [
                    'temperature' => (float) ($validated['temperature'] ?? 0.7),
                ],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu cấu hình agent AI.',
            'data' => $config,
        ]);
    }


    public function deleteFeatureConfig(string $featureKey): JsonResponse
    {
        $deleted = AIFeatureAgentConfig::query()->where('feature_key', $featureKey)->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted > 0,
            'feature_key' => $featureKey,
        ]);
    }

    public function getRequestLogFilters(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'feature_keys' => AIProviderRequestHistory::query()
                    ->whereNotNull('feature_key')
                    ->distinct()
                    ->orderBy('feature_key')
                    ->pluck('feature_key'),
                'agent_names' => AIProviderRequestHistory::query()
                    ->whereNotNull('agent_name')
                    ->distinct()
                    ->orderBy('agent_name')
                    ->pluck('agent_name'),
                'statuses' => AIProviderRequestHistory::query()
                    ->distinct()
                    ->orderBy('status')
                    ->pluck('status'),
            ],
        ]);
    }

    public function getRequestLogs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature_key' => 'nullable|string|max:120',
            'agent_name' => 'nullable|string|max:191',
            'status' => 'nullable|string|max:32',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 20;

        $logs = AIProviderRequestHistory::query()
            ->when(!empty($validated['feature_key']), fn ($q) => $q->where('feature_key', $validated['feature_key']))
            ->when(!empty($validated['agent_name']), fn ($q) => $q->where('agent_name', $validated['agent_name']))
            ->when(!empty($validated['status']), fn ($q) => $q->where('status', $validated['status']))
            ->latest()
            ->paginate($perPage)
            ->through(fn ($log) => [
                'id' => $log->id,
                'provider' => $log->provider,
                'model' => $log->model,
                'feature_key' => $log->feature_key,
                'agent_name' => $log->agent_name,
                'status' => $log->status,
                'http_status' => $log->http_status,
                'duration_ms' => $log->duration_ms,
                'created_at' => $log->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function getRequestLogDetail(string $id): JsonResponse
    {
        $log = AIProviderRequestHistory::query()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    public function intervene(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'world_id' => 'required|string',
            'instruction' => 'required|string|min:3|max:4000',
        ]);

        $record = AIProviderRequestHistory::query()->create([
            'provider' => 'system',
            'model' => null,
            'endpoint' => 'writer.ai.intervene',
            'feature_key' => 'writer.ai.intervene',
            'agent_name' => 'human-operator',
            'system_prompt' => null,
            'user_prompt' => $validated['instruction'],
            'request_payload' => json_encode([
                'world_id' => $validated['world_id'],
                'instruction' => $validated['instruction'],
            ], JSON_UNESCAPED_UNICODE),
            'response_payload' => json_encode([
                'accepted' => true,
            ], JSON_UNESCAPED_UNICODE),
            'http_status' => 202,
            'status' => 'ACCEPTED',
            'error_message' => null,
            'duration_ms' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Intervention accepted and recorded for audit.',
            'data' => [
                'accepted' => true,
                'request_log_id' => $record->id,
                'world_id' => $validated['world_id'],
            ],
        ], 202);
    }
}
