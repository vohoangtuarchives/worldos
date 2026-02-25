<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Actions;

use App\Modules\Narrative\Contracts\LLMChroniclerInterface;
use App\Modules\Narrative\Contracts\NarrativeSeriesRepositoryInterface;
use App\Modules\Narrative\Entities\SerialChapterEntity;
use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\ValueObjects\UniverseId;
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
            cascadeState: $universe->getCascadeState(),
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
