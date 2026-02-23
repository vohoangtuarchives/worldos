<?php

namespace App\Domains\Narrative\Character\Repositories;

use App\Domains\Narrative\Character\Character as CharacterAggregate;
use App\Domains\Narrative\Character\Entities\Memory;
use App\Domains\Narrative\Character\MemoryCollection;
use App\Domains\Narrative\Character\GoalStack;
use WorldOS\Saga\Domain\Narrative\ValueObject\EmotionState;
use App\Models\Character as CharacterModel;
use App\Models\CharacterMemory as CharacterMemoryModel;
use App\Models\CharacterEmotion as CharacterEmotionModel;
use App\Models\CharacterGoal as CharacterGoalModel;
use Illuminate\Support\Facades\DB;

class CharacterEloquentRepository
{
    public function save(CharacterAggregate $aggregate, string $worldId): void
    {
        DB::transaction(function () use ($aggregate, $worldId) {
            // 1. Save Character Core
            $model = CharacterModel::updateOrCreate(
                ['id' => $aggregate->getId()],
                [
                    'world_id' => $worldId,
                    'name' => $aggregate->getName(),
                    // 'base_personality' => ... (if handled in aggregate)
                ]
            );

            // 2. Sync Memories (Note: Append-only logic preferred, but updateOrCreate for simplicity now)
            foreach ($aggregate->getMemories()->all() as $memory) {
                CharacterMemoryModel::updateOrCreate(
                    ['id' => $memory->id],
                    [
                        'character_id' => $aggregate->getId(),
                        'type' => $memory->type,
                        'content' => $memory->content,
                        'visibility' => $memory->visibility,
                        'confidence' => $memory->confidence,
                        'timeline_node_id' => $memory->timelineNodeId,
                    ]
                );
            }

            // 3. Sync Emotions (Replace strategy or update)
            // Since emotions are dynamic and limited (one per type), we can updateOrCreate by type
            foreach ($aggregate->getEmotions() as $type => $emotionState) {
                CharacterEmotionModel::updateOrCreate(
                    [
                        'character_id' => $aggregate->getId(),
                        'type' => $type,
                    ],
                    [
                        'intensity' => $emotionState->intensity,
                        'decay_rate' => $emotionState->decayRate,
                    ]
                );
            }

            // 4. Sync Goals
            // Goals might be added/removed. For simplicity, we create new ones if not exist.
            // A more robust solution would be to track IDs in the goal stack.
            // Assuming simplified sync for now:
            foreach ($aggregate->getGoals()->all() as $goal) {
                CharacterGoalModel::updateOrCreate(
                    [
                        'character_id' => $aggregate->getId(),
                        'description' => $goal['description'], // Assuming description is unique key for now or just append
                    ],
                    [
                        'priority' => $goal['priority'],
                        'status' => $goal['status'],
                    ]
                );
            }
        });
    }

    public function findById(string $id): ?CharacterAggregate
    {
        $model = CharacterModel::with(['memories', 'emotions', 'goals'])->find($id);

        if (! $model) {
            return null;
        }

        // Reconstruct Memories
        $memories = new MemoryCollection();
        foreach ($model->memories as $m) {
            $memories->add(new Memory(
                $m->id,
                $m->type,
                $m->content,
                $m->visibility,
                (float) $m->confidence,
                $m->timeline_node_id
            ));
        }

        // Reconstruct Emotions
        $emotions = [];
        foreach ($model->emotions as $e) {
            $emotions[$e->type] = new EmotionState(
                $e->type,
                (float) $e->intensity,
                (float) $e->decay_rate
            );
        }

        // Reconstruct Goals
        $goals = new GoalStack();
        foreach ($model->goals as $g) {
            $goals->add([
                'description' => $g->description,
                'priority' => $g->priority,
                'status' => $g->status,
            ]);
        }

        return new CharacterAggregate(
            $model->id,
            $model->name,
            $memories,
            $emotions,
            $goals
        );
    }
}
