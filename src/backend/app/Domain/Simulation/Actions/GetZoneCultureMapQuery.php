<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Actions;

use Illuminate\Support\Facades\DB;

/**
 * Lấy lịch sử Snapshot mới nhất của Experiment chứa Cấu trúc Topology 
 * phục vụ cho việc Render HeatMap V6 trên Frontend
 */
class GetZoneCultureMapQuery
{
    public function execute(string $experimentId): array
    {
        $snapshot = DB::table('kernel_experiment_snapshots')
            ->where('experiment_id', $experimentId)
            ->whereNotNull('zone_topology_json')
            ->orderByDesc('tick')
            ->first();

        // Nếu chưa có Snapshot chạy qua Phase 3 (VD: tick=0, chưa chạy Rust)
        // thì trả về kết quả rỗng (Hoặc có thể sinh mẫu ban đầu)
        if (!$snapshot || empty($snapshot->zone_topology_json)) {
            return [
                'experiment_id' => $experimentId,
                'tick' => 0,
                'global_entropy' => 0.0,
                'topology' => null,
            ];
        }

        return [
            'experiment_id' => $experimentId,
            'tick' => $snapshot->tick,
            'global_entropy' => $snapshot->global_entropy,
            'topology' => json_decode($snapshot->zone_topology_json, true),
        ];
    }
}
