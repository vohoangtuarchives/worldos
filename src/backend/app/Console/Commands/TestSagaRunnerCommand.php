<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\World;
use App\Domains\Saga\Saga;
use App\Domains\Saga\SagaRunner;

class TestSagaRunnerCommand extends Command
{
    protected $signature = 'world:test-saga-runner';
    protected $description = 'Test the SagaRunner with Vietnamese Origin';

    public function handle(SagaRunner $runner)
    {
        $this->info("🚀 Starting SagaRunner Test...");

        // 1. Create Test Saga
        $saga = Saga::create([
            'name' => 'Runner Test ' . now()->timestamp,
            'status' => Saga::STATUS_PENDING,
            'world_count' => 1,
            'metadata' => [
                'origin_type' => 'vietnamese',
                'description' => 'Testing SagaRunner integration'
            ]
        ]);

        $this->info("✅ Created Saga: {$saga->name}");

        // 2. Run Sync
        // This simulates what RunSagaSimulationJob does
        try {
            $runner->runSync($saga);
            $this->info("✅ SagaRunner completed successfully.");
        } catch (\Exception $e) {
            $this->error("❌ SagaRunner failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
