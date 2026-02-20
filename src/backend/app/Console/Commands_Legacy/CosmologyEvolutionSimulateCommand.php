<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Cosmology\Evolution\SimulationConfig;
use App\Domains\Cosmology\Evolution\SimulationRunner;
use Illuminate\Console\Command;

class CosmologyEvolutionSimulateCommand extends Command
{
    protected $signature = 'cosmology:evolution-simulate
                            {--ticks=1000 : Number of ticks}
                            {--seed=42 : Random seed (for initial state)}
                            {--interval=50 : Snapshot every N ticks (0 = none)}
                            {--output=json : json or csv}';

    protected $description = 'Run deterministic evolution simulation (Arc + Preset, no LLM). Output metrics for tuning.';

    public function handle(SimulationRunner $runner): int
    {
        $ticks = (int) $this->option('ticks');
        $seed = (int) $this->option('seed');
        $interval = (int) $this->option('interval');
        $output = $this->option('output');

        $this->info("Running {$ticks} ticks (seed={$seed}, snapshot_interval={$interval})...");

        $config = new SimulationConfig($ticks, $interval, $seed);
        $result = $runner->run($config);

        $this->info('Arc transitions: ' . count($result->events));
        $this->info('Snapshots: ' . count($result->snapshots));

        if ($output === 'csv') {
            $this->outputCsv($result);
        } else {
            $this->outputJson($result);
        }

        return self::SUCCESS;
    }

    private function outputJson(\App\Domains\Cosmology\Evolution\SimulationResult $result): void
    {
        $this->line(json_encode([
            'metrics' => $result->metrics,
            'events' => $result->events,
            'snapshots_count' => count($result->snapshots),
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    private function outputCsv(\App\Domains\Cosmology\Evolution\SimulationResult $result): void
    {
        $this->line('tick,entropy,arc_phase');
        $n = count($result->metrics['tick']);
        for ($i = 0; $i < $n; $i++) {
            $this->line(sprintf(
                '%d,%s,%s',
                $result->metrics['tick'][$i],
                $result->metrics['entropy'][$i],
                $result->metrics['arc_phase'][$i]
            ));
        }
    }
}
