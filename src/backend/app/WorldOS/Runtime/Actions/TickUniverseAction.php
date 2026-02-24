<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Actions;

use App\WorldOS\Cosmology\Services\WorldEvolutionKernel;
use App\WorldOS\Cosmology\ValueObjects\EvolutionResult;
use App\WorldOS\Influence\Services\InfluencePipeline;
use App\WorldOS\Influence\ValueObjects\EvolutionContext;
use App\WorldOS\Runtime\Contracts\UniverseRepositoryInterface;
use App\WorldOS\Runtime\Contracts\UniverseSnapshotRepositoryInterface;
use App\WorldOS\Runtime\Dto\TickUniverseDTO;
use App\WorldOS\Runtime\Events\UniverseCollapsed;
use App\WorldOS\Runtime\Events\UniverseTicked;
use App\WorldOS\Runtime\Services\UniverseMutationService;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Runtime\ValueObjects\UniverseSnapshot;
use App\WorldOS\Style\Contracts\StyleRepositoryInterface;
use App\WorldOS\World\Contracts\WorldRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use LogicException;

/**
 * Tick Universe Action — 1 Use Case = 1 Tick.
 *
 * Orchestrates a single simulation tick:
 * 1. Load World (rules) + Universe (state)
 * 2. Apply Style overlay to LawVector (genre→physics)
 * 3. Call WorldEvolutionKernel::tick()
 * 4. Apply Influence forces via MutationService
 * 5. Apply evolution results to Universe
 * 6. Save snapshot
 * 7. Dispatch UniverseTicked (or UniverseCollapsed)
 */
final class TickUniverseAction
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly WorldRepositoryInterface $worldRepository,
        private readonly UniverseSnapshotRepositoryInterface $snapshotRepository,
        private readonly WorldEvolutionKernel $kernel,
        private readonly Dispatcher $eventDispatcher,
        private readonly InfluencePipeline $influencePipeline,
        private readonly UniverseMutationService $mutationService,
        private readonly StyleRepositoryInterface $styleRepository,
    ) {
    }

    public function handle(TickUniverseDTO $dto): EvolutionResult
    {
        // 1. Load Universe
        $universeId = UniverseId::fromString($dto->universeId);
        $universe = $this->universeRepository->findById($universeId);

        if ($universe === null) {
            throw new LogicException("Universe [{$dto->universeId}] not found");
        }

        if (!$universe->canTick()) {
            throw new LogicException(
                "Universe [{$dto->universeId}] cannot tick: status [{$universe->getStatus()->value}]"
            );
        }

        // 2. Load World (rules)
        $world = $this->worldRepository->findById($universe->getWorldId());

        if ($world === null) {
            throw new LogicException(
                "World [{$universe->getWorldId()}] not found for Universe [{$dto->universeId}]"
            );
        }

        if ($world->isHalted() || $world->isDead()) {
            throw new LogicException(
                "World [{$universe->getWorldId()}] is halted/dead — cannot tick Universe"
            );
        }

        // 3. Apply Style overlay (genre→physics) if active style exists
        $activeStyle = $this->styleRepository->findActiveByUniverseId($universeId);
        if ($activeStyle !== null) {
            $styledLaw = $activeStyle->calculatePhysicsOverlay($world->getLawVector());
            $world = $world->withOverriddenLaw($styledLaw);
        }

        // 4. Evolve via Kernel
        $result = $this->kernel->tick($world, $universe, $dto->cascadeThresholds);

        // 5. Apply Influence forces (Scars, Myths, Attractors)
        $influenceContext = new EvolutionContext(
            scars: [],    // Loaded by individual influences
            myths: [],
            attractors: [],
            laws: $world->getLawVector(),
        );
        $combinedForce = $this->influencePipeline->calculate(
            $result->newStateVector,
            $influenceContext,
        );

        // Apply influence via MutationService (magnitude-limited)
        $influencedState = $this->mutationService->applyInfluence($universe, $combinedForce);

        // 6. Apply evolution to Universe entity (use influenced state)
        $universe->applyEvolution($influencedState, $result->newCascadeState);
        $universe->incrementTick();

        // 5. Handle collapse
        if ($result->collapseDetected) {
            $universe->collapse($result->collapseReason ?? 'unknown');
        }

        // 6. Persist Universe
        $this->universeRepository->save($universe);

        // 7. Save snapshot (snapshot-first design)
        $snapshot = new UniverseSnapshot(
            universeId: $universeId,
            tick: $universe->getCurrentTick(),
            stateVector: $result->newStateVector,
            cascadeState: $result->newCascadeState,
            stability: $result->stability,
            metrics: $result->metrics,
            createdAt: new DateTimeImmutable(),
        );
        $this->snapshotRepository->save($snapshot);

        // 8. Dispatch domain events
        if ($result->collapseDetected) {
            $this->eventDispatcher->dispatch(new UniverseCollapsed(
                universeId: $universeId,
                tick: $universe->getCurrentTick(),
                cause: $result->collapseReason ?? 'unknown',
            ));
        } else {
            $this->eventDispatcher->dispatch(new UniverseTicked(
                universeId: $universeId,
                tick: $universe->getCurrentTick(),
                stateVector: $result->newStateVector,
                stabilityValue: $result->stability->value,
            ));
        }

        return $result;
    }
}
