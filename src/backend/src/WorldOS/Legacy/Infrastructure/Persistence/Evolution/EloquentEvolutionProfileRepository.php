<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Persistence\Evolution;

use WorldOS\Evolution\Domain\Legacy\Models\EvolutionProfile as EvolutionProfileModel;
use Illuminate\Contracts\Events\Dispatcher;
use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\Event\EvolutionProfileCreated;
use WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface;

final class EloquentEvolutionProfileRepository implements EvolutionProfileRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $models = EvolutionProfileModel::orderBy('updated_at', 'desc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = EvolutionProfile::create($model->name ?? '', $model->id);
        }
        return $result;
    }

    public function findById(string $id): ?EvolutionProfile
    {
        $model = EvolutionProfileModel::find($id);
        if ($model === null) {
            return null;
        }
        return EvolutionProfile::create($model->name ?? '', $model->id);
    }

    public function save(EvolutionProfile $profile): void
    {
        $model = EvolutionProfileModel::find($profile->getId());
        $isNew = $model === null;
        if ($model === null) {
            $model = new EvolutionProfileModel();
            $model->id = $profile->getId();
            $model->coefficients = [];
            $model->thresholds = [];
            $model->alpha = 1.0;
            $model->is_active = true;
        }
        $model->name = $profile->getName();
        $model->save();
        if ($isNew) {
            $this->dispatcher->dispatch(new EvolutionProfileCreated($profile->getId(), $profile->getName()));
        }
    }
}
