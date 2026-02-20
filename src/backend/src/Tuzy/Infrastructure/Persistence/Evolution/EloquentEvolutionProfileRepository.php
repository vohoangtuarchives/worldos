<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Evolution;

use App\Domains\Evolution\Models\EvolutionProfile as EvolutionProfileModel;
use Tuzy\Domain\Evolution\Entity\EvolutionProfile;
use Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface;

final class EloquentEvolutionProfileRepository implements EvolutionProfileRepositoryInterface
{
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
    }
}
