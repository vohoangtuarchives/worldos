<?php

declare(strict_types=1);

namespace App\WorldOS\Narrative\Actions;

use App\WorldOS\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\WorldOS\Narrative\Entities\NarrativeSeriesEntity;
use App\WorldOS\Narrative\Entities\StoryBibleEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Style\ValueObjects\GenreKey;

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
