<?php

namespace Tuzy\Infrastructure\WorldManagement\Repositories;

use App\Models\AiGeneration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class AIGovernanceRepository
{
    public function getRecentGenerations(int $limit = 20): Collection
    {
        return AiGeneration::with('world:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getGlobalMetrics(): array
    {
        $telemetry = DB::table('chapter_telemetry')
            ->selectRaw('SUM(prompt_tokens) as prompt, SUM(completion_tokens) as completion, SUM(total_tokens) as total')
            ->first();

        // Estimated cost (rough approximation based on GPT-4-turbo)
        $cost = ($telemetry->prompt / 1000 * 0.01) + ($telemetry->completion / 1000 * 0.03);

        return [
            'tokens' => [
                'prompt' => (int) $telemetry->prompt,
                'completion' => (int) $telemetry->completion,
                'total' => (int) $telemetry->total,
            ],
            'estimated_cost_usd' => round($cost, 4),
            'generations_count' => AiGeneration::count(),
            'success_rate' => $this->calculateSuccessRate(),
        ];
    }

    private function calculateSuccessRate(): float
    {
        $total = AiGeneration::count();
        if ($total === 0) return 1.0;
        
        $success = AiGeneration::where('status', 'ACCEPTED')->count();
        return round($success / $total, 2);
    }

    public function getAgentStats(): array
    {
        $summary = DB::table('ai_generations')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $roster = DB::table('ai_provider_request_histories')
            ->select('agent_name')
            ->selectRaw('COUNT(*) as requests')
            ->selectRaw('AVG(COALESCE(duration_ms, 0)) as avg_duration_ms')
            ->whereNotNull('agent_name')
            ->groupBy('agent_name')
            ->orderByDesc('requests')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                return [
                    'name' => $row->agent_name,
                    'status' => ((int) $row->requests) > 0 ? 'active' : 'idle',
                    'requests' => (int) $row->requests,
                    'avg_duration_ms' => round((float) $row->avg_duration_ms, 1),
                ];
            })
            ->values();

        return [
            'summary' => $summary,
            'roster' => $roster,
        ];
    }
}

