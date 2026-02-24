<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Application\AdvanceTick;

use WorldOS\Chronicle\Domain\Repository\ChronicleRepositoryInterface;
use WorldOS\Chronicle\Domain\Service\ChronicleWriter;
use WorldOS\Kernel\Domain\Policy\CompiledPolicy;
use WorldOS\Simulation\Domain\Engine\Service\EvolutionOperator;
use WorldOS\Simulation\Domain\Engine\Service\ForkDecider;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\UniverseSnapshot;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseId;
use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;
use DomainException;

/**
 * Handles AdvanceTickCommand.
 * Orchestrates: Universe → EvolutionOperator → TickResult → advanceTick() → Snapshot → Chronicle
 */
final class AdvanceTickHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface  $universeRepository,
        private readonly CompiledPolicy               $compiledPolicy,
        private readonly ChronicleRepositoryInterface $chronicleRepository,
        private readonly ChronicleWriter              $chronicleWriter,
        private readonly \WorldOS\Core\Substrate\GenesisService $genesisService,
        private readonly \WorldOS\Core\SimulationKernel $simulationKernel
    ) {
    }

    public function handle(AdvanceTickCommand $command): AdvanceTickResult
    {
        // 1. Load Universe
        $universeId = UniverseId::fromString($command->universeId);
        $universe   = $this->universeRepository->findById($universeId);

        if (!$universe) {
            throw new DomainException("Universe [{$command->universeId}] not found.");
        }

        // 2. Prepare Substrate
        $substrateHash = $this->genesisService->getSubstrateHash();
        if (!$substrateHash) {
            // Force sealing if not exists (fail-safe for legacy universes)
            $this->genesisService->sealUniverse();
            $substrateHash = $this->genesisService->getSubstrateHash();
        }
        $substrateEngine = new \WorldOS\Core\Substrate\SubstrateEngine($substrateHash);
        $substrateVector = $substrateEngine->getModifiers($universe->getCurrentTick() + 1);

        // 3. Create Simulation Snapshot
        $snapshot = new \WorldOS\Core\ValueObject\CivilizationSnapshot(
            $universe->getId()->toString(),
            $universe->getStateVector(),
            $universe->getIdeology(),
            $universe->getCulture(),
            $universe->getLifecycle(),
            $universe->getStabilityDuration(),
            $universe->getInfluenceMass()
        );

        // 4. Run the simulation kernel tick
        $simulationResult = $this->simulationKernel->tick($snapshot, $substrateVector);
        $nextSnapshot = $simulationResult->snapshot;

        // 5. Update Universe aggregate
        $universe->advanceTick(
            $nextSnapshot->physics,
            $nextSnapshot->ideology,
            $nextSnapshot->culture,
            $nextSnapshot->lifecycle,
            $nextSnapshot->influenceMass,
            $nextSnapshot->stabilityDuration
        );

        // 6. Persist updated Universe
        $this->universeRepository->save($universe);

        // 7. Legacy Mapping for TickResult (Bridge to Chronicle)
        // We calculate delta for compatibility with existing V5 Chronicle logic
        $entropyDelta = $nextSnapshot->physics->get(StateVector::DIMENSION_ENTROPY) - $snapshot->physics->get(StateVector::DIMENSION_ENTROPY);
        
        $tickResult = new \WorldOS\Simulation\Domain\Engine\ValueObject\TickResult(
            tick:            $universe->getCurrentTick(),
            seed:            $command->seed,
            nextStateVector: $nextSnapshot->physics,
            entropyDelta:    $entropyDelta,
            existenceWeight: 1.0, // Should use Policy evaluation if needed
            anomalies:       []
        );

        // 8. Capture UniverseSnapshot (Deterministic state copy)
        $capturedSnapshot = UniverseSnapshot::capture(
            universeId:      $universe->getId()->toString(),
            tick:            $universe->getCurrentTick(),
            seed:            $command->seed,
            entropy:         $universe->getEntropy(),
            stabilityIndex:  $universe->getStabilityIndex(),
            existenceWeight: 1.0,
            stateVector:     $nextSnapshot->physics
        );

        // 9. Chronicle: record physics-based events (legacy)
        $chronicleEvents = $this->chronicleWriter->record(
            universeId:    $universe->getId()->toString(),
            result:        $tickResult,
            forkTriggered: false
        );

        // BRIDGE: Add rich ontology events from SimulationKernel
        foreach ($simulationResult->emittedEvents as $kernelEvent) {
             $chronicleEvents[] = ChronicleEvent::record(
                universeId: $universe->getId()->toString(),
                tick:       $universe->getCurrentTick(),
                seed:       $command->seed,
                type:       EventType::from($kernelEvent['type'] ?? 'anomaly_spike'),
                title:      $kernelEvent['title'] ?? 'Sự kiện vô danh',
                severity:   Severity::from($kernelEvent['severity'] ?? 'medium'),
                payload:    $kernelEvent['payload'] ?? []
            );
        }

        foreach ($chronicleEvents as $event) {
            $this->chronicleRepository->save($event);
        }

        return new AdvanceTickResult(
            tickResult:      $tickResult,
            snapshot:        $capturedSnapshot,
            shouldFork:      false,
            forkPressure:    0.0,
            chronicleEvents: $chronicleEvents
        );
    }
}
