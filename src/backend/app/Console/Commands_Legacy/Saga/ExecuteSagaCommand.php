<?php

namespace App\Console\Commands\Saga;

use Tuzy\Domain\Saga\SagaExecutor;
use Tuzy\Application\Material\State\WorldStateRepository;
use Illuminate\Console\Command;

class ExecuteSagaCommand extends Command
{
    protected $signature = 'saga:execute
                            {--world= : World ID}
                            {--from=0 : Start epoch}
                            {--to= : End epoch}
                            {--type=full : Saga type (structural, symbolic, interaction, full)}';

    protected $description = 'Execute saga for world simulation';

    public function handle(
        SagaExecutor $executor,
        WorldStateRepository $stateRepository
    ): int {
        $worldId = $this->option('world');
        $from = (int) $this->option('from');
        $to = $this->option('to');
        $sagaType = $this->option('type');

        if (!$worldId) {
            $this->error('World ID required: --world=1');
            return 1;
        }

        $this->info("Executing saga for World {$worldId}");
        $this->info("Saga type: {$sagaType}");

        try {
            // Get current state
            $currentState = $stateRepository->getCurrentState($worldId);
            
            if (!$to) {
                $to = $currentState->epoch;
            }

            $this->info("Epoch range: {$from} → {$to}");

            $results = [];

            for ($epoch = $from + 1; $epoch <= $to; $epoch++) {
                $previousState = $stateRepository->reconstructState($worldId, $epoch - 1);
                $currentState = $stateRepository->reconstructState($worldId, $epoch);

                $result = $executor->execute(
                    $previousState,
                    $currentState,
                    $epoch,
                    $sagaType
                );

                $results[] = $result;

                // Display narrative
                $this->line($result['narrative']);

                if (!empty($result['events'])) {
                    $this->info("  Events: " . count($result['events']));
                    foreach ($result['events'] as $event) {
                        $this->warn("    - {$event['type']} (severity: {$event['severity']})");
                    }
                }

                $this->newLine();
            }

            $this->info("✅ Saga execution completed!");
            $this->info("Total epochs: " . count($results));
            $this->info("Total events: " . array_sum(array_column($results, 'event_count')));

            return 0;

        } catch (\Exception $e) {
            $this->error("Saga execution failed: {$e->getMessage()}");
            return 1;
        }
    }
}
