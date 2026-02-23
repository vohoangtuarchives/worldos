<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Application\Historian;

use WorldOS\Chronicle\Domain\Repository\ChronicleRepositoryInterface;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseId;
use WorldOS\Saga\Domain\Legacy\NarrativeAssembler;

/**
 * DraftChronicleHandler — Orchestrates the generation of a historical summary.
 */
final class DraftChronicleHandler
{
    public function __construct(
        private readonly ChronicleRepositoryInterface $repository,
        private readonly UniverseRepositoryInterface  $universeRepository,
        private readonly NarrativeAssembler         $assembler,
    ) {
    }

    /**
     * Generate the "Great Chronicle" for a given universe.
     */
    public function handle(string $universeId): string
    {
        // 1. Fetch universe to get current tick context
        $universe = $this->universeRepository->findById(UniverseId::fromString($universeId));
        $upToTick = $universe ? $universe->getCurrentTick() : null;

        // 2. Fetch all events for the universe (ordered by tick)
        $events = $this->repository->findByUniverse($universeId, limit: 1000);

        if (empty($events)) {
            return "Vũ trụ này chưa có lịch sử được ghi lại. Một tờ giấy trắng đang chờ được lấp đầy...";
        }

        // 3. Use Assembler (which uses Historian internally) to build the text
        return $this->assembler->assembleChronicle($events, $upToTick);
    }
}
