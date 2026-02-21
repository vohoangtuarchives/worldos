<?php

namespace App\Console\Commands\Reader;

use Illuminate\Console\Command;

class ExecuteReaderInteractionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reader:execute
                            {world : World ID}
                            {epoch : Epoch number}
                            {--apply : Apply deltas to WorldState}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute reader interaction cycle for an epoch';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $worldId = (int) $this->argument('world');
        $epoch = (int) $this->argument('epoch');
        $apply = $this->option('apply');

        $this->info("Executing reader interaction for World {$worldId}, Epoch {$epoch}");

        try {
            $engine = app(\Tuzy\Domain\Reader\ReaderInteractionEngine::class);

            // Execute interaction cycle
            $result = $engine->execute($worldId, $epoch);

            // Display results
            $this->displayResults($result);

            // Apply deltas if requested
            if ($apply && !empty($result['deltas'])) {
                $this->applyDeltas($worldId, $epoch, $result['deltas']);
            }

            $this->info('✓ Reader interaction executed successfully');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to execute reader interaction: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Display interaction results.
     */
    private function displayResults(array $result): void
    {
        $this->newLine();
        $this->line('=== Reader Interaction Results ===');
        $this->newLine();

        // Choices
        $this->line("Choices Generated: " . count($result['choices']));
        foreach ($result['choices'] as $choice) {
            $this->line("  - {$choice['question']}");
        }

        $this->newLine();

        // Votes
        $this->line("Vote Results:");
        foreach ($result['votes'] as $vote) {
            $this->line("  Choice: {$vote['choice_id']}");
            $this->line("    Total Votes: {$vote['total_votes']}");
            if ($vote['winner']) {
                $this->line("    Winner: {$vote['winner']}");
            }
        }

        $this->newLine();

        // Reactions
        $this->line("Reactions: {$result['reactions']}");

        $this->newLine();

        // Deltas
        $this->line("Calculated Deltas (dampening: {$result['dampening_factor']}x):");
        if (empty($result['deltas'])) {
            $this->line("  None");
        } else {
            foreach ($result['deltas'] as $field => $value) {
                $sign = $value >= 0 ? '+' : '';
                $this->line("  {$field}: {$sign}{$value}");
            }
        }

        $this->newLine();
    }

    /**
     * Apply deltas to WorldState.
     */
    private function applyDeltas(string $worldId, int $epoch, array $deltas): void
    {
        $this->info('Applying deltas to WorldState...');

        $repository = app(\Tuzy\Application\Material\State\WorldStateRepository::class);
        $mutator = app(\Tuzy\Application\Material\State\WorldStateMutator::class);

        // Get current state
        $currentState = $repository->getCurrentState($worldId);

        // Apply deltas
        $newState = $mutator->applyDeltas($currentState, $deltas, ['reader_influence']);

        // Save event
        $repository->saveEvent($worldId, $epoch, $deltas, ['reader_influence'], [
            'source' => 'reader_interaction',
            'dampening_factor' => 0.5,
        ]);

        $this->info('✓ Deltas applied successfully');
    }
}
