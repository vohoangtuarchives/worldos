<?php

namespace WorldOS\Legacy\Application\Simulator\UseCase;

use WorldOS\Evolution\Domain\Legacy\Repository\WorldStateRepository;
use WorldOS\Evolution\Domain\Legacy\Repository\CivilizationStateRepository;
use WorldOS\Legacy\Infrastructure\EventBus\EventBus;
use WorldOS\Evolution\Domain\Legacy\Service\WorldEvolutionPipeline;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldSnapshot;
use WorldOS\Legacy\Domain\Cosmology\Repository\WorldRepository;
use WorldOS\Legacy\Domain\Cosmology\Entity\World;
use WorldOS\Legacy\Domain\Cosmology\Enum\Archetype;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldField;
use WorldOS\Evolution\Domain\Legacy\Enum\WorldPhase;
use WorldOS\Evolution\Domain\Legacy\ValueObject\LifeState;

class StepWorldUseCase
{
    public function __construct(
        private WorldStateRepository $worldStateRepo,
        private CivilizationStateRepository $civStateRepo,
        private WorldRepository $worldRepo, // Thêm WorldRepo
        private \WorldOS\Legacy\Infrastructure\EventBus\EventBus $eventBus,
        private \WorldOS\Evolution\Domain\Legacy\Service\WorldEvolutionPipeline $pipeline,
        private \WorldOS\Legacy\Infrastructure\Realtime\RealtimeStreamServer $streamServer // NEW
    ) {}

    public function execute(string $worldId, int $ticks = 1, int $deltaYears = 1): array
    {
        $worldState = $this->worldStateRepo->findById($worldId);
        if (!$worldState) {
            throw new \Exception("WorldState not found for world " . $worldId);
        }

        // V4 Restoration: Load World entity to get Physical Laws
        $world = $this->worldRepo->findById($worldId);
        if (!$world) {
            // Fallback for blind-run testing if World not created
            $world = new World($worldId, "World {$worldId}", Archetype::HIGH_FANTASY);
        }


        $civStates = $this->civStateRepo->findByWorld($worldId);
        if (count($civStates) === 0) {
            throw new \Exception("No CivilizationState found for world " . $worldId);
        }

        // Simplification: We simulate the first civilization found
        $civState = $civStates[0];

        // Optimized check: Does this world exist in the persistent DB?
        // If not (e.g. In-Memory testing), we skip DB-backed Chronicle events to avoid FK violations.
        $worldExistsInDb = \Illuminate\Support\Facades\DB::table('worlds')
            ->where('id', $worldId)
            ->exists();

        $logs = [];

        for ($i = 0; $i < $ticks; $i++) {
            // 1. Build WorldSnapshot from Aggregates
            $currentSnapshot = new WorldSnapshot(
                cosmic: $worldState->getCosmicState(),
                environment: $worldState->getEnvironmentState(),
                civilization: $civState->getSnapshot(),
                worldField: WorldField::default(),
                worldPhase: WorldPhase::CIVILIZATIONAL_AGE,
                lifeState: $worldState->getLifeState(),
                year: $worldState->getYear()
            );

            // 2. Run Master Pipeline
            $agentPerturbation = 0.0; // Can be injected later from user actions
            $nextSnapshot = $this->pipeline->step($world, $currentSnapshot, $agentPerturbation, $deltaYears);


            // 3. Update Aggregates
            $worldState->updateFromSnapshot($nextSnapshot);
            $civState->updateFromSnapshot($nextSnapshot->civilization);

            // 4. Extract Events from Pipeline
            $pipelineEvents = $this->pipeline->getLastStepEvents();
            foreach ($pipelineEvents as $evt) {
                // V3 Event format varies, usually has 'type'
                $type = $evt['type'] ?? 'Unknown Event';
                $intensity = $evt['intensity'] ?? 0;
                $logs[] = "Year [{$nextSnapshot->year}]: Event Erupted [{$type}] with intensity {$intensity}";

                // Save to Chronicle DB
                try {
                    if ($worldExistsInDb) {
                        \App\Models\WorldChronicleEvent::create([
                            'world_id' => $worldId,
                            'year' => $nextSnapshot->year,
                            'type' => $type,
                            'title' => $evt['name'] ?? $type,
                            'description' => $evt['description'] ?? '',
                            'severity' => $intensity >= 1.0 ? 'CRITICAL' : ($intensity >= 0.8 ? 'HIGH' : ($intensity >= 0.5 ? 'MEDIUM' : 'LOW')),
                            'metadata' => $evt['metadata'] ?? [],
                        ]);
                    }

                    // Tích hợp Real-time Chronicle Stream (Giai đoạn 18)
                    $this->streamServer->broadcastEvent([
                        'world_id' => $worldId,
                        'year' => $nextSnapshot->year,
                        'type' => $type,
                        'title' => $evt['name'] ?? $type,
                        'description' => $evt['description'] ?? '',
                        'severity' => $intensity >= 1.0 ? 'CRITICAL' : ($intensity >= 0.8 ? 'HIGH' : ($intensity >= 0.5 ? 'MEDIUM' : 'LOW')),
                        'metadata' => $evt['metadata'] ?? [],
                    ], $worldId);
                    
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to save/broadcast Chronicle Event: " . $e->getMessage());
                }
            }

            // (Optional) Decay internal civilization residual memory
            if ($i % 10 === 0) {
                $civState->ageEra();
            }

            // 5. Broadcast Hyper-Realtime Update (SSE/MessagePack)
            $this->streamServer->broadcastUpdate($nextSnapshot, $worldId);

            // 6. Dispatch V4 Domain Events (e.g. EntropyCriticalReached from WorldState)
            $v4Events = $worldState->releaseEvents();
            if (count($v4Events) > 0) {
                $this->eventBus->dispatchAll($v4Events);
                foreach ($v4Events as $e) {
                    $className = class_basename($e);
                    if ($className === 'EntropyCriticalReached') {
                        $logs[] = "Year [{$nextSnapshot->year}]: GLOBAL ENTROPY CRITICAL REACHED!";
                    } else {
                        $logs[] = "Year [{$nextSnapshot->year}]: Domain Event dispatched: {$className}";
                    }
                }
            }
        }

        // 6. Save State
        $this->worldStateRepo->save($worldState);
        $this->civStateRepo->save($civState);

        return $logs;
    }
}
