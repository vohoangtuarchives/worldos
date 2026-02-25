<?php

declare(strict_types=1);

namespace App\Modules\Universe\Actions;

use App\Modules\Shared\ValueObjects\LawVector;
use App\Modules\Universe\Contracts\WorldRepositoryInterface;
use App\Modules\Universe\Dto\DefineWorldDTO;
use App\Modules\Universe\Entities\WorldEntity;
use App\Modules\Universe\Events\WorldDefined;
use App\Modules\Universe\ValueObjects\WorldId;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Use Case: Define a new World from a preset + optional law overrides.
 *
 * Creates the WorldEntity, persists it, and dispatches WorldDefined event.
 */
final class DefineWorldAction
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
        private readonly Dispatcher $eventDispatcher,
    ) {
    }

    public function handle(DefineWorldDTO $dto): WorldEntity
    {
        // 1. Build LawVector from preset + overrides
        // For now, use overrides directly; preset system will be added in Phase 6
        $lawVector = LawVector::fromArray($dto->lawOverrides);

        // 2. Create domain entity
        $world = new WorldEntity(
            id: WorldId::generate(),
            name: $dto->name,
            lawVector: $lawVector,
            presetKey: $dto->presetKey,
            originType: $dto->originType,
            createdAt: new DateTimeImmutable(),
        );

        // 3. Persist
        $this->worldRepository->save($world);

        // 4. Dispatch domain event
        $this->eventDispatcher->dispatch(new WorldDefined(
            worldId: $world->getId(),
            name: $world->getName(),
            lawVector: $world->getLawVector(),
            presetKey: $world->getPresetKey(),
        ));

        return $world;
    }
}
