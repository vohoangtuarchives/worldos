<?php

namespace Tuzy\Application\Narrative\Timeline\Repositories;

use App\Models\TimelineNode as TimelineNodeModel;
use Tuzy\Application\Narrative\Timeline\TimelineNode;
use Tuzy\Domain\Narrative\ValueObject\StateSnapshot;

class TimelineEloquentRepository
{
    public function save(TimelineNode $node, string $worldId): void
    {
        // 1. Create or Update Node
        TimelineNodeModel::updateOrCreate(
            ['id' => $node->id],
            [
                'world_id' => $worldId,
                'parent_ids' => $node->parentIds,
                'canonical_level' => $node->canonicalLevel,
                'state_snapshot' => $node->snapshot->toArray(),
            ]
        );
    }

    public function findById(string $id): ?TimelineNode
    {
        $model = TimelineNodeModel::find($id);
        if (!$model) return null;

        return new TimelineNode(
            $model->id,
            $model->parent_ids ?? [],
            $model->canonical_level,
            StateSnapshot::fromArray($model->state_snapshot ?? [])
        );
    }
}
