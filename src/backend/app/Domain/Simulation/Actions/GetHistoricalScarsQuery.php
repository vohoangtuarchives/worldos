<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Actions;

use Illuminate\Support\Facades\DB;

class GetHistoricalScarsQuery
{
    /**
     * Dùng cho mục đích truy vấn Historical Scars từ CSDL hoặ Topology Map
     */
    public function execute(string $experimentId): array
    {
        // Trong kiến trúc hiện tại, Vết thương lịch sử được ghim vào Zone Topology.
        // Action này đọc từ Topology Json để parse ra List các Scars
        $snapshot = DB::table('kernel_experiment_snapshots')
            ->where('experiment_id', $experimentId)
            ->whereNotNull('zone_topology_json')
            ->orderByDesc('tick')
            ->first();

        if (!$snapshot || empty($snapshot->zone_topology_json)) {
            return [];
        }

        $topology = json_decode($snapshot->zone_topology_json, true);
        $scars = [];

        // Parsing "zones" from the Universe JSON SlotMap
        // {"zones": {"id_1": {"material": {...}, "scars": [...]}}}
        if (isset($topology['zones'])) {
            foreach ($topology['zones'] as $zoneId => $zoneData) {
                // Giả định structure của zone
                if (isset($zoneData['myth_scars']) && is_array($zoneData['myth_scars'])) {
                    foreach ($zoneData['myth_scars'] as $scar) {
                        $scars[] = [
                            'zone_id' => $zoneId,
                            'scar' => $scar,
                        ];
                    }
                }
            }
        }

        return $scars;
    }
}
