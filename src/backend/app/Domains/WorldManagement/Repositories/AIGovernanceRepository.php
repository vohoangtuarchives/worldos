<?php

namespace App\Domains\WorldManagement\Repositories;

use App\Models\AiGeneration;
use App\Models\ChapterTelemetry;
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
        // Grouping by context_type if available, or using a stub for now
        $stats = DB::table('ai_generations')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return [
            'summary' => $stats,
            'roster' => [
                ['name' => 'Chronicler', 'status' => 'idle', 'throughput' => '0.5 tps'],
                ['name' => 'Critic', 'status' => 'active', 'throughput' => '1.2 tps'],
                ['name' => 'Planner', 'status' => 'idle', 'throughput' => '0.2 tps'],
            ]
        ];
    }
}
