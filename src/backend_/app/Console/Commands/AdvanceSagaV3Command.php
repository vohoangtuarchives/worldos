<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use WorldOS\Legacy\Application\Saga\Services\SagaService;
use WorldOS\Saga\Domain\Legacy\Saga;

class AdvanceSagaV3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saga:advance-v3 {--saga-id= : Specific Saga ID (optional)} {--ticks=1 : Number of ticks to advance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advance active Sagas using WorldOS V3 Evolution Kernel';

    /**
     * Execute the console command.
     */
    public function handle(SagaService $sagaService): int
    {
        $sagaId = $this->option('saga-id');
        $ticks = (int) $this->option('ticks');

        $this->info("🚀 Starting V3 Saga Advance (Ticks: {$ticks})...");

        if ($sagaId) {
            $sagas = Saga::where('id', $sagaId)->get();
        } else {
            $sagas = Saga::where('status', 'running')->get();
        }

        if ($sagas->isEmpty()) {
            $this->info("ℹ️ No active sagas found.");
            return Command::SUCCESS;
        }

        foreach ($sagas as $saga) {
            $this->info("➡️ Processing Saga: {$saga->name} ({$saga->id})");
            try {
                $sagaService->runBatchWithEvaluation($saga, $ticks);
                $this->info("   ✅ Advanced successfully.");
            } catch (\Throwable $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
                // Continue to next saga even if one fails
            }
        }

        $this->info("🏁 V3 Advance Cycle Complete.");
        return Command::SUCCESS;
    }
}
