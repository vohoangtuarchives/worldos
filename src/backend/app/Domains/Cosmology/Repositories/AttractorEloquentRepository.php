<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Repositories;

use App\Domains\Cosmology\Aggregates\AttractorAggregate;
use App\Domains\Cosmology\Contracts\AttractorRepositoryInterface;
use App\Domains\Cosmology\ValueObjects\AttractorIncarnation;
use Illuminate\Support\Facades\DB;

class AttractorEloquentRepository implements AttractorRepositoryInterface
{
    public function findByCode(string $code): ?AttractorAggregate
    {
        $row = DB::table('attractors')->where('code', $code)->first();

        return $row ? $this->rowToAggregate($row) : null;
    }

    public function findById(string $id): ?AttractorAggregate
    {
        $row = DB::table('attractors')->where('id', $id)->first();

        return $row ? $this->rowToAggregate($row) : null;
    }

    public function save(AttractorAggregate $attractor): void
    {
        DB::table('attractors')->updateOrInsert(
            ['id' => $attractor->id],
            [
                'code' => $attractor->code,
                'name' => $attractor->name,
                'lifecycle_state' => $attractor->lifecycleState,
                'historical_inertia' => json_encode($attractor->historicalInertia),
                'cumulative_rebirth_gain' => $attractor->cumulativeRebirthGain,
                'identity_karma_index' => $attractor->identityKarmaIndex,
                'phase_state' => $attractor->phaseState,
                'current_incarnation_id' => $attractor->getCurrentIncarnationId(),
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }

    public function saveIncarnation(AttractorIncarnation $incarnation): void
    {
        DB::table('attractor_incarnations')->updateOrInsert(
            ['id' => $incarnation->id],
            [
                'attractor_id' => $incarnation->attractorId,
                'parent_incarnation_id' => $incarnation->parentIncarnationId,
                'start_tick' => $incarnation->startTick,
                'end_tick' => $incarnation->endTick,
                'centroid_snapshot' => json_encode($incarnation->centroidSnapshot),
                'semantic_snapshot' => json_encode($incarnation->semanticSnapshot),
                'basin_radius' => $incarnation->basinRadius,
                'curvature_factor' => $incarnation->curvatureFactor,
                'rebirth_gain_from_parent' => $incarnation->rebirthGainFromParent,
                'morph_intensity' => $incarnation->morphIntensity,
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }

    public function getCurrentIncarnation(string $attractorId): ?AttractorIncarnation
    {
        $row = DB::table('attractor_incarnations')
            ->where('attractor_id', $attractorId)
            ->whereNull('end_tick')
            ->orderByDesc('start_tick')
            ->first();

        return $row ? $this->rowToIncarnation($row) : null;
    }

    public function getIncarnationTree(string $attractorId): array
    {
        $rows = DB::table('attractor_incarnations')
            ->where('attractor_id', $attractorId)
            ->orderBy('start_tick')
            ->get();

        return $rows->map(fn($r) => $this->rowToIncarnation($r))->all();
    }

    public function closeIncarnation(string $incarnationId, int $endTick): void
    {
        DB::table('attractor_incarnations')
            ->where('id', $incarnationId)
            ->update(['end_tick' => $endTick, 'updated_at' => now()]);
    }

    private function rowToAggregate($row): AttractorAggregate
    {
        $agg = new AttractorAggregate(
            id: $row->id,
            code: $row->code,
            name: $row->name,
            lifecycleState: $row->lifecycle_state,
            historicalInertia: json_decode($row->historical_inertia ?? '[]', true),
            cumulativeRebirthGain: $row->cumulative_rebirth_gain,
            identityKarmaIndex: $row->identity_karma_index,
            phaseState: $row->phase_state,
            currentIncarnationId: $row->current_incarnation_id
        );

        // Load incarnations
        $incarnations = $this->getIncarnationTree($row->id);
        $agg->loadIncarnations($incarnations);

        return $agg;
    }

    private function rowToIncarnation($row): AttractorIncarnation
    {
        return AttractorIncarnation::fromArray([
            'id' => $row->id,
            'attractor_id' => $row->attractor_id,
            'parent_incarnation_id' => $row->parent_incarnation_id,
            'start_tick' => $row->start_tick,
            'end_tick' => $row->end_tick,
            'centroid_snapshot' => json_decode($row->centroid_snapshot, true),
            'semantic_snapshot' => json_decode($row->semantic_snapshot ?? '[]', true),
            'basin_radius' => $row->basin_radius,
            'curvature_factor' => $row->curvature_factor,
            'rebirth_gain_from_parent' => $row->rebirth_gain_from_parent,
            'morph_intensity' => $row->morph_intensity,
        ]);
    }
}
