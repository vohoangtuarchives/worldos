<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Repositories;

use App\Models\NarrativeSeriesModel;
use App\Modules\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\Modules\Narrative\Entities\NarrativeSeriesEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Style\ValueObjects\GenreKey;

class NarrativeSeriesEloquentRepository implements NarrativeSeriesRepositoryInterface
{
    public function findById(string $id): ?NarrativeSeriesEntity
    {
        $model = NarrativeSeriesModel::find($id);

        return $model ? $this->modelToEntity($model) : null;
    }

    public function save(NarrativeSeriesEntity $series): void
    {
        $model = NarrativeSeriesModel::find($series->getId()) ?? new NarrativeSeriesModel();

        $model->id = $series->getId();
        $model->universe_id = $series->getUniverseId()->value;
        $model->genre = $series->getGenre()->value;
        $model->title = $series->getTitle();
        $model->current_book_index = $series->getCurrentBookIndex();
        $model->total_chapters_generated = $series->getTotalChaptersGenerated();
        $model->require_arc_approval = $series->requiresArcApproval();
        $model->is_active = $series->isActive();
        $model->save();
    }

    /**
     * @return NarrativeSeriesEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array
    {
        return NarrativeSeriesModel::where('universe_id', $universeId->value)
            ->get()
            ->map(fn(NarrativeSeriesModel $m) => $this->modelToEntity($m))
            ->all();
    }

    public function findActiveByUniverseId(UniverseId $universeId): ?NarrativeSeriesEntity
    {
        $model = NarrativeSeriesModel::where('universe_id', $universeId->value)
            ->where('is_active', true)
            ->first();

        return $model ? $this->modelToEntity($model) : null;
    }

    private function modelToEntity(NarrativeSeriesModel $model): NarrativeSeriesEntity
    {
        return new NarrativeSeriesEntity(
            id: $model->id,
            universeId: new UniverseId($model->universe_id),
            genre: GenreKey::from($model->genre),
            title: $model->title,
            currentBookIndex: $model->current_book_index,
            totalChaptersGenerated: $model->total_chapters_generated,
            requireArcApproval: $model->require_arc_approval,
            isActive: $model->is_active,
        );
    }
}
