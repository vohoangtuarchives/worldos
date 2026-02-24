<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Application\ForkTimeline;

use WorldOS\Simulation\Domain\Universe\Entity\Universe;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseId;
use DomainException;

/**
 * Handles ForkTimelineCommand.
 * Creates a child Universe branching from the current state of the parent.
 * This is the DAG branching mechanism — the parent continues; the child starts from parent's last StateVector.
 */
final class ForkTimelineHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository
    ) {
    }

    /**
     * @return Universe The newly forked child Universe
     */
    public function handle(ForkTimelineCommand $command): Universe
    {
        // 1. Load parent Universe
        $parentId = UniverseId::fromString($command->parentUniverseId);
        $parent   = $this->universeRepository->findById($parentId);

        if (!$parent) {
            throw new DomainException("Parent Universe [{$command->parentUniverseId}] not found.");
        }

        // 2. Create the forked child Universe from parent's current state
        $childName = sprintf(
            "%s::fork@tick-%d",
            $parent->getName(),
            $parent->getCurrentTick()
        );

        $child = Universe::spawn(
            name:              $childName,
            worldBlueprintId:  $parent->getWorldBlueprintId(),
            initialStateVector: $parent->getCurrentStateVector(),
        );

        // Start the child Universe immediately
        $child->start();

        // 3. Persist the forked child
        $this->universeRepository->save($child);

        return $child;
    }
}
