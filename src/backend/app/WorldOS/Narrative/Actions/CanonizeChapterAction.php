<?php

declare(strict_types=1);

namespace App\WorldOS\Narrative\Actions;

use App\WorldOS\Narrative\Entities\SerialChapterEntity;
use App\WorldOS\Narrative\Services\NarrativeFeedbackService;
use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use LogicException;

final class CanonizeChapterAction
{
    public function __construct(
        private readonly NarrativeFeedbackService $feedbackService,
        private readonly UniverseRepositoryInterface $universeRepository,
    ) {
    }

    /**
     * Canonize a chapter: mark CANONIZED → create WorldMyths → feedback loop.
     *
     * @return array{chapter: SerialChapterEntity, myths: array}
     */
    public function handle(
        SerialChapterEntity $chapter,
        UniverseId $universeId,
    ): array {
        // 1. Canonize chapter
        $chapter->canonize();

        // 2. Load universe for current tick
        $universe = $this->universeRepository->findById($universeId);

        if ($universe === null) {
            throw new LogicException("Universe [{$universeId->value}] not found");
        }

        // 3. Feed back into simulation (canonized text → WorldMyths)
        $myths = $this->feedbackService->processCanonization(
            chapter: $chapter,
            universeId: $universeId,
            currentTick: $universe->getCurrentTick(),
        );

        return [
            'chapter' => $chapter,
            'myths' => $myths,
        ];
    }
}
