<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use WorldOS\Simulation\Application\AdvanceTick\AdvanceTickCommand;
use WorldOS\Simulation\Application\AdvanceTick\AdvanceTickHandler;
use WorldOS\Chronicle\Application\Historian\DraftChronicleHandler;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseId;

final class SimulateWorldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worldos:simulate 
                            {universe? : ID of the universe to simulate} 
                            {--ticks=10 : Number of ticks to advance} 
                            {--seed= : Random seed for simulation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate Universe evolution and read the Great Chronicle';

    public function __construct(
        private readonly AdvanceTickHandler         $advanceTickHandler,
        private readonly DraftChronicleHandler       $draftChronicleHandler,
        private readonly UniverseRepositoryInterface $universeRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $universeIdString = $this->argument('universe');
        $ticks = (int) $this->option('ticks');
        $seed = $this->option('seed') ? (int) $this->option('seed') : random_int(1, 999999);

        // 1. Resolve Universe
        if (!$universeIdString) {
            $this->warn('No Universe ID provided. Checking for existing universes...');
            // In a real scenario, we might find the last modified. 
            // For now, let's just fail if not found or tell user to create one.
            $this->error('Please provide a Universe ID. Use "php artisan simulation:spawn-universe" first.');
            return self::FAILURE;
        }

        $this->info("Starting simulation for Universe [{$universeIdString}]...");
        $this->info("Target: {$ticks} ticks | Seed: {$seed}");

        $bar = $this->output->createProgressBar($ticks);
        $bar->start();

        // 2. Run simulation loop
        try {
            for ($i = 0; $i < $ticks; $i++) {
                $command = new AdvanceTickCommand(
                    universeId: $universeIdString,
                    seed:       $seed + $i
                );
                $this->advanceTickHandler->handle($command);
                $bar->advance();
            }
        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Simulation interrupted: " . $e->getMessage());
            return self::FAILURE;
        }

        $bar->finish();
        $this->newLine(2);

        // 3. Generate and Display Chronicle
        $this->info('=== TRUYỀN KỲ SỬ KÝ (THE GREAT CHRONICLE) ===');
        $this->newLine();
        
        $chronicle = $this->draftChronicleHandler->handle($universeIdString);
        
        $this->line($chronicle);
        
        $this->newLine();
        $this->info('=== KẾT THÚC BẢN THẢO ===');

        return self::SUCCESS;
    }
}
