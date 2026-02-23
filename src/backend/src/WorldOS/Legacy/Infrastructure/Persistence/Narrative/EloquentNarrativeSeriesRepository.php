<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Persistence\Narrative;

use App\Models\NarrativeSeries as NarrativeSeriesModel;
use Illuminate\Contracts\Events\Dispatcher;
use WorldOS\Saga\Domain\Narrative\Entity\NarrativeSeries;
use WorldOS\Saga\Domain\Narrative\Event\NarrativeSeriesCreated;
use WorldOS\Saga\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class EloquentNarrativeSeriesRepository implements NarrativeSeriesRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $models = NarrativeSeriesModel::orderBy('updated_at', 'desc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = NarrativeSeries::create($model->title ?? '', $model->id);
        }
        return $result;
    }

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
        $isNew = $model === null;
        if ($model === null) {
            $model = new NarrativeSeriesModel();
            $model->id = $series->getId();
            $model->genre_key = 'fantasy_school';
        }
        $model->title = $series->getTitle();
        $model->save();
        if ($isNew) {
            $this->dispatcher->dispatch(new NarrativeSeriesCreated($series->getId(), $series->getTitle()));
        }
    }
}
