<?php

declare(strict_types=1);

namespace App\WorldOS\Style\Repositories;

use App\Models\UniverseStyleModel;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Style\Contracts\StyleRepositoryInterface;
use App\WorldOS\Style\Entities\UniverseStyleEntity;
use App\WorldOS\Style\ValueObjects\GenreKey;
use App\WorldOS\Style\ValueObjects\StyleVector;

class StyleEloquentRepository implements StyleRepositoryInterface
{
    public function findById(string $id): ?UniverseStyleEntity
    {
        $model = UniverseStyleModel::find($id);

        return $model ? $this->modelToEntity($model) : null;
    }

    public function save(UniverseStyleEntity $style): void
    {
        $model = UniverseStyleModel::find($style->getId()) ?? new UniverseStyleModel();

        $model->id = $style->getId();
        $model->universe_id = $style->getUniverseId()->value;
        $model->genre = $style->getGenre()->value;
        $model->style_vector = $style->getStyleVector()->toArray();
        $model->name = $style->getName();
        $model->version = $style->getVersion();
        $model->is_active = $style->isActive();
        $model->save();
    }

    public function findActiveByUniverseId(UniverseId $universeId): ?UniverseStyleEntity
    {
        $model = UniverseStyleModel::where('universe_id', $universeId->value)
            ->where('is_active', true)
            ->first();

        return $model ? $this->modelToEntity($model) : null;
    }

    /**
     * @return UniverseStyleEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array
    {
        return UniverseStyleModel::where('universe_id', $universeId->value)
            ->get()
            ->map(fn(UniverseStyleModel $m) => $this->modelToEntity($m))
            ->all();
    }

    private function modelToEntity(UniverseStyleModel $model): UniverseStyleEntity
    {
        return new UniverseStyleEntity(
            id: $model->id,
            universeId: new UniverseId($model->universe_id),
            genre: GenreKey::from($model->genre),
            styleVector: StyleVector::fromArray($model->style_vector),
            name: $model->name,
            version: $model->version,
            isActive: $model->is_active,
        );
    }
}
