<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Narrative;

use App\Models\NarrativeSeries as NarrativeSeriesModel;
use Tuzy\Domain\Narrative\Entity\NarrativeSeries;
use Tuzy\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class EloquentNarrativeSeriesRepository implements NarrativeSeriesRepositoryInterface
{
    public function findById(string $id): ?NarrativeSeries
    {
        $model = NarrativeSeriesModel::find($id);
        if ($model === null) {
            return null;
        }
        return NarrativeSeries::create($model->title ?? '', $model->id);
    }

    public function save(NarrativeSeries $series): void
    {
        $model = NarrativeSeriesModel::find($series->getId());
        if ($model === null) {
            $model = new NarrativeSeriesModel();
            $model->id = $series->getId();
            $model->genre_key = 'fantasy_school';
        }
        $model->title = $series->getTitle();
        $model->save();
    }
}
