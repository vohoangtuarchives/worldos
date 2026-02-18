<?php

namespace App\Jobs;

use App\Domains\Cosmology\Contracts\CivilizationSnapshotRepositoryInterface;
use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Evolution\ArcDetector;
use App\Domains\Cosmology\Evolution\ArcMemory;
use App\Domains\Cosmology\Evolution\ArcPhase;
use App\Domains\Cosmology\Evolution\PresetTransitionEngine;
use App\Domains\Cosmology\Evolution\RegimeModifier;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Cosmology\Services\AttractorInfluenceRecorder;
use App\Domains\Cosmology\Services\BasePhysicsEngine;
use App\Domains\Cosmology\Services\FactionSurvivalService;
use App\Domains\Cosmology\Services\StructuralMutationEngine;
use App\Domains\Narrative\Chronicle\ChronicleEventWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TickUniverseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const SNAPSHOT_EVERY_N_TICKS = 10;
    private const LOCK_SECONDS = 60;

    public function __construct(
        private readonly string $universeId,
        private readonly int $ticksToRun = 1
    ) {
    }

    public function handle(
        CosmologyRepository $repository,
        BasePhysicsEngine $kernel,
        ArcDetector $arcDetector,
        PresetTransitionEngine $presetEngine,
        StructuralMutationEngine $mutationEngine,
        ?CivilizationSnapshotRepositoryInterface $snapshotRepo = null,
        ?FactionSurvivalService $factionSurvivalService = null,
        ?ChronicleEventWriter $chronicleWriter = null,
        ?AttractorInfluenceRecorder $influenceRecorder = null
    ): void {
        $lockKey = 'cosmology:universe:' . $this->universeId;
        $lock = Cache::lock($lockKey, self::LOCK_SECONDS);

        if (!$lock->get()) {
            Log::info("TickUniverseJob: could not acquire lock for universe {$this->universeId}, skipping.");
            return;
        }

        try {
            $universe = $repository->find($this->universeId);
            if ($universe === null) {
                Log::warning("TickUniverseJob: universe {$this->universeId} not found.");
                return;
            }

            $state = $universe->getState();
            $age = $universe->getAge();
            $params = $universe->getParameters();
            $coords = $universe->getCoords();
            $factionId = $universe->getCosmicFactionId();

            $arcPhase = ArcPhase::tryFrom($params['arc_phase'] ?? '') ?? ArcPhase::EXPANSION;
            $arcTicks = (int) ($params['arc_ticks_in_phase'] ?? 0);
            $arcMemory = new ArcMemory($arcPhase, $arcTicks);

            for ($i = 0; $i < $this->ticksToRun; $i++) {
                $stateBefore = $state;
                $ageBefore = $age;

                $detected = $arcDetector->detect($state);
                $effectivePhase = $arcMemory->considerTransition($detected);
                $arcMemory = $arcMemory->advance($effectivePhase);
                $preset = $presetEngine->resolve($arcMemory->current);
                $regime = RegimeModifier::forPhase($arcMemory->current);

                $state = $kernel->evolve($state, $preset, $regime);
                $age++;

                $assessment = $kernel->getLastAssessment();
                if ($assessment !== null && !empty($assessment['should_collapse'])) {
                    $pressure = $assessment['pressure'] ?? 0.9;
                    $state = $mutationEngine->mutate($state, $pressure);
                    if ($factionSurvivalService !== null) {
                        $factionSurvivalService->onUniverseCollapse($this->universeId, $age);
                    }
                }

                if ($influenceRecorder !== null) {
                    $influenceRecorder->record($this->universeId, $age, $state);
                }

                if ($snapshotRepo !== null) {
                    $diff = $state->subtract($stateBefore)->getAll();
                    $snapshotRepo->saveDiff($this->universeId, $ageBefore, $age, $diff);
                    if ($age > 0 && $age % self::SNAPSHOT_EVERY_N_TICKS === 0) {
                        $snapshotRepo->saveSnapshot($this->universeId, $age, null, null, $state);
                        if ($chronicleWriter !== null) {
                            $chronicleWriter->write('SNAPSHOT', $age, "universe:{$this->universeId}:tick:{$age}", $state->getAll(), 'cosmology', $this->universeId);
                        }
                    }
                }
            }

            $params['arc_phase'] = $arcMemory->current->value;
            $params['arc_ticks_in_phase'] = $arcMemory->ticksInPhase;
            $updated = new Universe($state, $params, $this->universeId, $age, $coords, $factionId);
            $repository->save($updated);
        } finally {
            $lock->release();
        }
    }
}
