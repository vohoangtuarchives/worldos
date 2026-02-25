<?php

declare(strict_types=1);

namespace App\Modules\Universe\Actions;

use App\Modules\Simulation\Services\Grpc\SimulationEngineGrpcClient;
use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\Contracts\UniverseSnapshotRepositoryInterface;
use App\Modules\Universe\Dto\TickUniverseDTO;
use App\Modules\Universe\Events\UniverseCollapsed;
use App\Modules\Universe\Events\UniverseTicked;
use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\Universe\ValueObjects\UniverseSnapshot;
use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\WorldStateVector;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use LogicException;
use App\Modules\Universe\Contracts\WorldRepositoryInterface;

/**
 * Tick Universe Action
 *
 * Orchestrates a single simulation tick using the Rust gRPC Simulation Engine.
 */
final class TickUniverseAction
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly UniverseSnapshotRepositoryInterface $snapshotRepository,
        private readonly SimulationEngineGrpcClient $grpcClient,
        private readonly Dispatcher $eventDispatcher,
        private readonly WorldRepositoryInterface $worldRepository,
    ) {
    }

    public function handle(TickUniverseDTO $dto): array
    {
        $universeId = UniverseId::fromString($dto->universeId);
        $universe = $this->universeRepository->findById($universeId);

        if ($universe === null) {
            throw new LogicException("Universe [{$dto->universeId}] not found");
        }

        $world = $this->worldRepository->findById($universe->getWorldId());
        
        if ($world === null) {
            throw new LogicException("World [{$universe->getWorldId()->value}] not found for Universe [{$dto->universeId}]");
        }

        if (!$universe->canTick()) {
            throw new LogicException("Universe [{$dto->universeId}] cannot tick: status [{$universe->getStatus()->value}]");
        }

        // 1. Get parameters — prefer World config, fall back to Universe params
        $worldConfig = $world->getConfig() ?? [];
        $params = array_merge($universe->getParameters(), $worldConfig);
        $aMatrix = $params['a_matrix'] ?? ($params['aMatrix'] ?? [0.0, 0.0, 0.0, 0.0]);
        $lMatrix = $params['l_matrix'] ?? ($params['lMatrix'] ?? [1.0, -1.0, -1.0, 1.0]);
        // Detect dimension from matrix size (sqrt of flat array length)
        $dim = (int) sqrt(count($aMatrix));
        if ($dim < 2) { $dim = 2; }
        $controlVector = $params['controlVector'] ?? array_fill(0, $dim, 0.0);

        // 2. Extract current state
        // WorldStateVector.toArray() returns named keys — flatten to indexed float array for gRPC
        $currentState = array_values($universe->getStateVector()->toArray());
        // dim is the length of the state vector (always 6 for WorldStateVector)
        $dim = count($currentState);
        // Adjust matrices and control vector to match actual dim
        $expectedMatrixSize = $dim * $dim;
        if (count($aMatrix) !== $expectedMatrixSize) {
            // Pad or reseed aMatrix and lMatrix to correct dimension
            $aMatrix = array_fill(0, $expectedMatrixSize, 0.0);
            $lMatrix = array_fill(0, $expectedMatrixSize, 0.0);
        }
        if (count($controlVector) !== $dim) {
            $controlVector = array_fill(0, $dim, 0.0);
        }
        $currentCascade = $universe->getCascadeState()?->toArray() ?? [
            'physics' => 0.0, 'chemistry' => 0.0, 'biology' => 0.0, 'cognition' => 0.0, 'culture' => 0.0
        ];
        $cascadeThresholds = $world->getCascadeThresholds()?->toArray() ?? [
            'physics_to_chemistry' => 0.3, 'chemistry_to_biology' => 0.4, 'biology_to_cognition' => 0.5, 'cognition_to_culture' => 0.6
        ];
        $lawVector = $world->getLawVector()->toArray();

        // 3. Call gRPC Engine
        $response = $this->grpcClient->runTick(
            $dto->universeId,
            $dim,
            $currentState,
            $controlVector,
            $aMatrix,
            $lMatrix,
            $params['alpha'] ?? 0.1,
            $params['lambda'] ?? 0.5,
            $params['eta'] ?? 0.01,
            $params['beta'] ?? 1.0,
            $params['deltaTarget'] ?? 0.1,
            $params['gammaCap'] ?? 2.0,
            $params['rMax'] ?? 10.0,
            $params['energyRateLimit'] ?? 1.5,
            array_values($currentCascade),
            array_values($cascadeThresholds),
            array_values($lawVector)
        );

        if (!$response['success']) {
            $reason = $response['error_message'];
            $universe->collapse($reason);
            $this->universeRepository->save($universe);
            
            $this->eventDispatcher->dispatch(new UniverseCollapsed(
                universeId: $universeId,
                tick: $universe->getCurrentTick(),
                cause: $reason,
            ));
            
            return ['status' => 'rejected', 'reason' => $reason];
        }

        // 4. Update Universe state
        $nextState = WorldStateVector::fromArray($response['next_state']);
        $cascadeRes = $response['next_cascade_state'] ?? [];
        
        $nextCascadeArray = [
            'physics' => $cascadeRes[0] ?? 0.0,
            'chemistry' => $cascadeRes[1] ?? 0.0,
            'biology' => $cascadeRes[2] ?? 0.0,
            'cognition' => $cascadeRes[3] ?? 0.0,
            'culture' => $cascadeRes[4] ?? 0.0,
        ];
        $newCascade = CascadeStateVector::fromArray($nextCascadeArray);
        
        $universe->applyEvolution($nextState, $newCascade);
        $universe->incrementTick();

        $this->universeRepository->save($universe);

        // 5. Save Snapshot
        $snapshot = new UniverseSnapshot(
            universeId: $universeId,
            tick: $universe->getCurrentTick(),
            stateVector: $nextState,
            cascadeState: $newCascade,
            stability: null,
            metrics: null,
            createdAt: new DateTimeImmutable(),
        );
        $this->snapshotRepository->save($snapshot);

        // 6. Dispatch Event
        $this->eventDispatcher->dispatch(new UniverseTicked(
            universeId: $universeId,
            tick: $universe->getCurrentTick(),
            stateVector: $nextState,
            stabilityValue: 1.0, // placeholder
        ));

        return [
            'status' => 'success',
            'next_state' => $response['next_state']
        ];
    }
}
