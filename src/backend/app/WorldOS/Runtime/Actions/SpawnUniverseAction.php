<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Actions;

use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\Contracts\UniverseSnapshotRepositoryInterface;
use App\WorldOS\Runtime\Dto\SpawnUniverseDTO;
use App\WorldOS\Runtime\Entities\UniverseEntity;
use App\WorldOS\Runtime\Events\UniverseForked;
use App\WorldOS\Runtime\Events\UniverseSpawned;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Runtime\ValueObjects\UniverseSnapshot;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\Seed;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use App\WorldOS\World\Contracts\WorldRepositoryInterface;
use App\WorldOS\World\ValueObjects\WorldId;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use LogicException;

/**
 * Use Case: Spawn a new Universe from a World blueprint.
 *
 * Supports both fresh spawn and fork from existing universe.
 */
final class SpawnUniverseAction
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly UniverseSnapshotRepositoryInterface $snapshotRepository,
        private readonly Dispatcher $eventDispatcher,
    ) {
    }

    public function handle(SpawnUniverseDTO $dto): UniverseEntity
    {
        // 1. Load World blueprint
        $world = $this->worldRepository->findById(WorldId::fromString($dto->worldId));

        if ($world === null) {
            throw new InvalidArgumentException("World [{$dto->worldId}] not found");
        }

        if (!$world->canSpawnUniverse()) {
            throw new LogicException(
                "World [{$dto->worldId}] cannot spawn: status is [{$world->getStatus()->value}]"
            );
        }

        // 2. Determine initial state
        $seed = $dto->seed !== null ? new Seed($dto->seed) : Seed::generate();
        $parentUniverseId = $dto->parentUniverseId !== null
            ? UniverseId::fromString($dto->parentUniverseId)
            : null;

        if ($parentUniverseId !== null) {
            // Fork mode: copy state from parent at fork tick
            return $this->forkUniverse($dto, $world, $seed, $parentUniverseId);
        }

        // Fresh spawn: initial state
        $initialState = WorldStateVector::zero();
        $initialCascade = CascadeStateVector::initial();

        // 3. Create Universe entity
        $universe = new UniverseEntity(
            id: UniverseId::generate(),
            worldId: $world->getId(),
            stateVector: $initialState,
            cascadeState: $initialCascade,
            seed: $seed,
            parentUniverseId: null,
            createdAt: new DateTimeImmutable(),
            parameters: $dto->parameters,
        );

        // 4. Persist
        $this->universeRepository->save($universe);

        // 5. Create initial snapshot (tick 0)
        $this->snapshotRepository->save(new UniverseSnapshot(
            universeId: $universe->getId(),
            tick: 0,
            stateVector: $initialState,
            cascadeState: $initialCascade,
            stability: null,
            metrics: null,
            createdAt: new DateTimeImmutable(),
        ));

        // 6. Dispatch event
        $this->eventDispatcher->dispatch(new UniverseSpawned(
            universeId: $universe->getId(),
            worldId: $world->getId(),
            seed: $seed,
            parentUniverseId: null,
        ));

        return $universe;
    }

    private function forkUniverse(
        SpawnUniverseDTO $dto,
        mixed $world,
        Seed $seed,
        UniverseId $parentUniverseId,
    ): UniverseEntity {
        $parent = $this->universeRepository->findById($parentUniverseId);

        if ($parent === null) {
            throw new InvalidArgumentException(
                "Parent universe [{$parentUniverseId}] not found"
            );
        }

        // Determine fork state
        $forkTick = $dto->forkAtTick ?? $parent->getCurrentTick();
        $snapshot = $this->snapshotRepository->findByTick($parentUniverseId, $forkTick);

        if ($snapshot === null) {
            throw new InvalidArgumentException(
                "No snapshot found for universe [{$parentUniverseId}] at tick [{$forkTick}]"
            );
        }

        // Create forked Universe with parent's state at fork point
        $universe = new UniverseEntity(
            id: UniverseId::generate(),
            worldId: $world->getId(),
            stateVector: $snapshot->stateVector,
            cascadeState: $snapshot->cascadeState ?? CascadeStateVector::initial(),
            seed: $seed,
            parentUniverseId: $parentUniverseId,
            createdAt: new DateTimeImmutable(),
            currentTick: $forkTick,
            age: $forkTick,
            parameters: $dto->parameters,
        );

        $this->universeRepository->save($universe);

        // Create initial snapshot for fork
        $this->snapshotRepository->save(new UniverseSnapshot(
            universeId: $universe->getId(),
            tick: $forkTick,
            stateVector: $snapshot->stateVector,
            cascadeState: $snapshot->cascadeState,
            stability: $snapshot->stability,
            metrics: $snapshot->metrics,
            createdAt: new DateTimeImmutable(),
        ));

        $this->eventDispatcher->dispatch(new UniverseSpawned(
            universeId: $universe->getId(),
            worldId: $world->getId(),
            seed: $seed,
            parentUniverseId: $parentUniverseId,
        ));

        $this->eventDispatcher->dispatch(new UniverseForked(
            childUniverseId: $universe->getId(),
            parentUniverseId: $parentUniverseId,
            forkTick: $forkTick,
        ));

        return $universe;
    }
}
