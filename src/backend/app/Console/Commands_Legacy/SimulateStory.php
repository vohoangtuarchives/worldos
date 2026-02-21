<?php

namespace App\Console\Commands;

use App\StoryEngine\Simulator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SimulateStory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'story:simulate {--chapters=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate the story engine for N chapters';

    /**
     * Execute the console command.
     */
    public function handle(Simulator $simulator)
    {
        $chapters = (int) $this->option('chapters');
        $this->info("Simulating for {$chapters} chapters...");

        $metrics = $simulator->run($chapters);

        $outputPath = storage_path('logs/story_metrics.json');
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, json_encode($metrics, JSON_PRETTY_PRINT));

        $this->info("Simulation complete. Metrics saved to: {$outputPath}");

        // Summarize results
        $lastMetric = end($metrics);
        $this->table(
            ['Metric', 'Final Value'],
            [
                ['Chapters', $lastMetric['chapter']],
                ['Active Seeds', $lastMetric['active_seeds']],
                ['Max Dimension', $lastMetric['max_dimension']],
                ['Power Tier', $lastMetric['power_tier']],
                ['Public Awareness', $lastMetric['public_awareness']],
            ]
        );
    }
}
