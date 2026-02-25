<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Actions;

use App\Modules\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\Modules\Narrative\Entities\NarrativeSeriesEntity;
use App\Modules\Narrative\Entities\StoryBibleEntity;
use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\WorldTemplate\ValueObjects\GenreKey;

final class CreateSeriesAction
{
    public function __construct(
        private readonly NarrativeSeriesRepositoryInterface $seriesRepository,
    ) {
    }

    public function handle(
        UniverseId $universeId,
        GenreKey $genre,
        string $title,
        bool $requireArcApproval = true,
    ): NarrativeSeriesEntity {
        // Check if active series already exists
        $existing = $this->seriesRepository->findActiveByUniverseId($universeId);
        if ($existing !== null) {
            // Deactivate existing before creating new
            $existing->deactivate();
            $this->seriesRepository->save($existing);
        }

        $series = NarrativeSeriesEntity::create(
            universeId: $universeId,
            genre: $genre,
            title: $title,
            requireArcApproval: $requireArcApproval,
        );

        $this->seriesRepository->save($series);

        return $series;
    }
}
