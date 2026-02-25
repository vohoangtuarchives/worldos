<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Simulation\Actions\TickUniverseAction;

class TestSimulationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulation:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Simulation Engine through gRPC with dummy data';

    /**
     * Execute the console command.
     */
    public function handle(TickUniverseAction $action)
    {
        $this->info("Initializing the Simulation Engine test...");

        // Dummy Dimension N = 2
        $dimension = 2;
        $universeId = 'test-universe-001';

        // x(t)
        $currentState = [0.5, 0.5];
        
        // u(t)
        $controlVector = [0.1, 0.0];

        // Flat A Matrix (2x2 Zero Matrix → stable, Gershgorin 0.899 <= 0.9)
        $aMatrix = [
            0.0, 0.0,
            0.0, 0.0,
        ];

        // Flat L Matrix (Graph Laplacian, say a single edge)
        $lMatrix = [
            1.0, -1.0,
            -1.0, 1.0,
        ];

        $config = [
            'dimension' => $dimension,
            'alpha' => 0.1,
            'lambda' => 0.5,
            'eta' => 0.01,
            'beta' => 1.0,
            'deltaTarget' => 0.1,
            'gammaCap' => 2.0,
            'rMax' => 10.0,
            'energyRateLimit' => 1.5,
        ];

        $this->info("Sending TickRequest to Universe {$universeId}");

        try {
            $result = $action->handle(
                $universeId,
                $config,
                $currentState,
                $controlVector,
                $aMatrix,
                $lMatrix
            );

            if ($result['status'] === 'success') {
                $this->info("✅ Success! Next State:");
                $this->line(json_encode($result['next_state'], JSON_PRETTY_PRINT));
            } else {
                $this->error("❌ Rejected by Governance Guard: " . $result['reason']);
            }
        } catch (\Exception $e) {
            $this->error("❌ gRPC Command Failed: " . $e->getMessage());
        }
    }
}
