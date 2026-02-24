<?php

declare(strict_types=1);

namespace App\WorldOS\Narrative\Actions;

use App\WorldOS\Narrative\Contracts\LLMChroniclerInterface;
use App\WorldOS\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\WorldOS\Narrative\Entities\SerialChapterEntity;
use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use LogicException;

final class GenerateChapterAction
{
    public function __construct(
        private readonly NarrativeSeriesRepositoryInterface $seriesRepository,
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly LLMChroniclerInterface $chronicler,
    ) {
    }

    public function handle(string $seriesId): SerialChapterEntity
    {
        $series = $this->seriesRepository->findById($seriesId);

        if ($series === null) {
            throw new LogicException("Series [{$seriesId}] not found");
        }

        if (!$series->isActive()) {
            throw new LogicException("Series [{$seriesId}] is not active");
        }

        // Load universe for current state
        $universe = $this->universeRepository->findById($series->getUniverseId());

        if ($universe === null) {
            throw new LogicException(
                "Universe [{$series->getUniverseId()->value}] not found for Series [{$seriesId}]"
            );
        }

        // Generate chapter text via LLMChronicler
        $rawText = $this->chronicler->chronicle(
            state: $universe->getStateVector(),
            genre: $series->getGenre(),
            events: [], // TODO: load recent WorldEvents
            context: [],
        );

        // Create chapter entity
        $chapter = SerialChapterEntity::draft(
            seriesId: $seriesId,
            bookIndex: $series->getCurrentBookIndex(),
            chapterIndex: $series->getTotalChaptersGenerated() + 1,
            rawText: $rawText,
        );

        // Update series counter
        $series->recordChapterGenerated();
        $this->seriesRepository->save($series);

        return $chapter;
    }
}
