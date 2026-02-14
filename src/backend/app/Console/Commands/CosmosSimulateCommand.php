<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\World;
use App\Domains\Cosmic\Services\WaveInterferenceEngine;
use App\Domains\Cosmic\Services\CosmicEvolutionService;
use App\Domains\Cosmic\Services\BifurcationManager;
use App\Domains\Cosmic\Services\WorldEvolutionPipeline;
use App\Domains\Cosmic\Services\CosmicNarrativeRenderer;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Domains\Cosmic\Repositories\CosmicSnapshotEloquentRepository;

class CosmosSimulateCommand extends Command
{
    protected $signature = 'cosmos:simulate
        {world_id : The ID of the world to simulate}
        {--eras=100 : Number of eras (time steps) to simulate}
        {--delta=100 : Years per time step}
        {--snapshot-interval=10 : Save snapshot every N steps}
        {--narrate : Show narrative for each snapshot}
        {--resume : Resume from latest snapshot instead of year 0}';

    protected $description = 'Run the deterministic cosmic simulation for a world';

    public function handle(): int
    {
        $worldId = (int) $this->argument('world_id');
        $eras = (int) $this->option('eras');
        $delta = (int) $this->option('delta');
        $interval = (int) $this->option('snapshot-interval');
        $narrate = $this->option('narrate');
        $resume = $this->option('resume');

        $world = World::find($worldId);
        if (!$world) {
            $this->error("World #{$worldId} not found.");
            return 1;
        }

        $this->info("🌌 Cosmic Simulation: {$world->name}");
        $this->info("   Eras: {$eras} | Δt: {$delta} years | Snapshot interval: {$interval}");

        // Build pipeline
        $waveEngine = new WaveInterferenceEngine();
        $cosmicService = new CosmicEvolutionService($waveEngine);
        $bifurcationManager = new BifurcationManager();
        $pipeline = new WorldEvolutionPipeline($cosmicService, $bifurcationManager);
        $repository = new CosmicSnapshotEloquentRepository();
        $renderer = $narrate ? new CosmicNarrativeRenderer() : null;

        // Initial state
        $startYear = 0;
        $snapshot = null;

        if ($resume) {
            $snapshot = $repository->latestSnapshot($worldId);
            if ($snapshot) {
                $startYear = $snapshot->year;
                $this->info("   📍 Resuming from year {$startYear}");
            }
        }

        if (!$snapshot) {
            $snapshot = WorldSnapshot::defaultObservation($startYear);
        }

        // Save initial snapshot
        $repository->saveSnapshot($worldId, $snapshot);

        $bar = $this->output->createProgressBar($eras);
        $bar->start();

        $totalEvents = 0;

        for ($i = 0; $i < $eras; $i++) {
            $snapshot = $pipeline->step($snapshot, 0.0, $delta);

            // Save snapshot at intervals
            if (($i + 1) % $interval === 0 || $i === $eras - 1) {
                $repository->saveSnapshot($worldId, $snapshot);
            }

            // Save events
            $stepEvents = $pipeline->getLastStepEvents();
            foreach ($stepEvents as $event) {
                $repository->saveEvent($worldId, $event);
                $totalEvents++;

                if ($narrate) {
                    $bar->clear();
                    $this->newLine();
                    $this->warn("   💥 " . ($event['type'] ?? 'EVENT') . " at year {$event['year']}");
                    $this->info("      {$event['from']} → {$event['to']}");
                    $bar->display();
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Final summary
        $this->info("✅ Simulation complete.");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Final Year', $snapshot->year],
                ['Attractor', $snapshot->cosmic->currentAttractor],
                ['Energy', round($snapshot->cosmic->energy, 4)],
                ['Entropy', round($snapshot->cosmic->entropy, 4)],
                ['Strain', round($snapshot->cosmic->strain, 4)],
                ['Stability', round($snapshot->cosmic->stability, 4)],
                ['Composite Tension', round($snapshot->compositeTension(), 4)],
                ['Civ Knowledge', round($snapshot->civilization->collectiveKnowledge, 4)],
                ['Resonance', round($snapshot->civilization->resonanceAccumulator, 4)],
                ['Bifurcation Events', $totalEvents],
            ]
        );

        if ($narrate && $renderer) {
            $this->newLine();
            $this->info("📜 Narrative:");
            $narrative = $renderer->render($snapshot, $pipeline->getLastStepEvents());
            $this->line("   🌀 " . $narrative['cosmic']);
            $this->line("   🌍 " . $narrative['environment']);
            $this->line("   🏛️ " . $narrative['civilization']);
            $this->line("   " . $narrative['tension']);
        }

        // Update world's current tick
        $world->update([
            'current_tick' => $snapshot->year,
            'entropy' => $snapshot->cosmic->entropy,
        ]);

        return 0;
    }
}
