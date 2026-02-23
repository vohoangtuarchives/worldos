<?php

namespace App\Console\Commands\Performance;

use Illuminate\Console\Command;
use WorldOS\Legacy\Application\Material\Engine\MaterialLawEngine;
use Illuminate\Support\Facades\DB;

/**
 * BatchSimulationCommand - Run batch simulation with optimizations
 */
class BatchSimulationCommand extends Command
{
    protected $signature = 'simulate:batch
                            {world : World ID}
                            {epochs : Number of epochs to simulate}
                            {--batch-size=10 : Commit events every N epochs}
                            {--memory-limit=512 : Memory limit in MB}';

    protected $description = 'Run batch simulation with performance optimizations';

    public function handle(): int
    {
        $worldId = (int) $this->argument('world');
        $epochs = (int) $this->argument('epochs');
        $batchSize = (int) $this->option('batch-size');
        $memoryLimit = (int) $this->option('memory-limit') * 1024 * 1024;

        $this->info("Starting batch simulation for World {$worldId}");
        $this->info("Epochs: {$epochs}, Batch Size: {$batchSize}");

        $engine = app(MaterialLawEngine::class);
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $bar = $this->output->createProgressBar($epochs);
        $bar->start();

        $eventBuffer = [];

        for ($epoch = 1; $epoch <= $epochs; $epoch++) {
            // Check memory limit
            $currentMemory = memory_get_usage();
            if ($currentMemory > $memoryLimit) {
                $this->newLine();
                $this->warn("Memory limit reached at epoch {$epoch}");
                $this->warn("Current: " . $this->formatBytes($currentMemory));
                
                // Force garbage collection
                gc_collect_cycles();
                
                $afterGC = memory_get_usage();
                $this->info("After GC: " . $this->formatBytes($afterGC));
            }

            // Process tick
            try {
                $result = $engine->processTick($worldId, $epoch);
                
                // Buffer events
                $eventBuffer[] = [
                    'world_id' => $worldId,
                    'epoch' => $epoch,
                    'result' => $result,
                ];

                // Batch commit
                if (count($eventBuffer) >= $batchSize) {
                    $this->commitEvents($eventBuffer);
                    $eventBuffer = [];
                }

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed at epoch {$epoch}: " . $e->getMessage());
                return self::FAILURE;
            }

            $bar->advance();
        }

        // Commit remaining events
        if (!empty($eventBuffer)) {
            $this->commitEvents($eventBuffer);
        }

        $bar->finish();
        $this->newLine(2);

        // Performance stats
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $duration = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        $epochsPerSecond = $epochs / $duration;

        $this->line('=== Performance Statistics ===');
        $this->line("Total Time: " . round($duration, 2) . "s");
        $this->line("Epochs/Second: " . round($epochsPerSecond, 2));
        $this->line("Memory Used: " . $this->formatBytes($memoryUsed));
        $this->line("Peak Memory: " . $this->formatBytes(memory_get_peak_usage()));

        $this->info('✓ Batch simulation completed successfully');

        return self::SUCCESS;
    }

    private function commitEvents(array $events): void
    {
        DB::beginTransaction();
        
        try {
            foreach ($events as $event) {
                // Commit event to database
                // (Implementation depends on your event structure)
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
