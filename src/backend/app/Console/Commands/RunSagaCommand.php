<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tuzy\Domain\Saga\Saga;
use Tuzy\Domain\Saga\SagaRunner;

class RunSagaCommand extends Command
{
    protected $signature = 'saga:run
                            {name : Name of the saga}
                            {--worlds=5 : Number of worlds to run}
                            {--archetypes=* : Archetype keys to focus on}
                            {--carry : Carry legacy between worlds}';

    protected $description = 'Run a multi-world saga with archetype legacy transfer';

    private SagaRunner $sagaRunner;

    public function __construct()
    {
        parent::__construct();
        $this->sagaRunner = new SagaRunner();
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $worldCount = (int) $this->option('worlds');
        $archetypes = $this->option('archetypes');
        $carryLegacy = $this->option('carry') ?? true;

        $this->info("🌀 Starting Saga: {$name}");
        $this->info("📊 Configuration:");
        $this->info("   Worlds: {$worldCount}");
        
        if (!empty($archetypes)) {
            $this->info("   Archetype Focus: " . implode(', ', $archetypes));
        }
        
        $this->info("   Legacy Transfer: " . ($carryLegacy ? 'Enabled' : 'Disabled'));
        $this->newLine();

        // Create saga
        $saga = Saga::create([
            'name' => $name,
            'world_count' => $worldCount,
            'archetype_focus' => !empty($archetypes) ? $archetypes : null,
            'carry_legacy' => $carryLegacy,
            'status' => Saga::STATUS_PENDING,
        ]);

        // Start saga
        $this->sagaRunner->start($saga);

        $this->info("✅ Saga started!");
        $this->info("   Saga ID: {$saga->id}");
        $this->newLine();

        // Monitor progress
        $this->monitorSaga($saga);

        return Command::SUCCESS;
    }

    /**
     * Monitor saga execution
     */
    private function monitorSaga(Saga $saga): void
    {
        $this->info("📡 Monitoring saga execution...");
        $this->newLine();

        while (true) {
            $saga->refresh();
            $status = $this->sagaRunner->getStatus($saga);

            $this->displayStatus($status);

            if ($saga->isComplete()) {
                $this->displayCompletion($saga);
                break;
            }

            sleep(2); // Check every 2 seconds
        }
    }

    /**
     * Display saga status
     */
    private function displayStatus(array $status): void
    {
        $current = $status['progress']['current'];
        $total = $status['progress']['total'];
        $percent = $status['progress']['percentage'];

        $this->line("Progress: World {$current}/{$total} ({$percent}%)");
        
        // Display world statuses
        foreach ($status['worlds'] as $world) {
            $icon = $world['collapsed'] ? '💥' : '✅';
            $statusText = $world['status'];
            
            $this->line("  {$icon} World #{$world['sequence']}: {$statusText}");
        }

        $this->newLine();
    }

    /**
     * Display completion summary
     */
    private function displayCompletion(Saga $saga): void
    {
        $this->info("🎉 Saga Complete!");
        $this->newLine();

        $collapseCount = $saga->getCollapseCount();
        $survivedCount = $saga->world_count - $collapseCount;

        $this->info("📊 Summary:");
        $this->info("   Total Worlds: {$saga->world_count}");
        $this->info("   Collapsed: {$collapseCount}");
        $this->info("   Survived: {$survivedCount}");
        $this->info("   Observations: {$saga->observations()->count()}");
        $this->newLine();

        // Display observations
        if ($saga->observations()->count() > 0) {
            $this->info("🔍 Key Observations:");
            
            foreach ($saga->observations()->take(5)->get() as $observation) {
                $this->line("   • {$observation->observation}");
            }
        }
    }
}
