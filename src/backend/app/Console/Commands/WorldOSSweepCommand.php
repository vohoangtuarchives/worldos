<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Application\Kernel\SimulationRunner;

class WorldOSSweepCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Support range inputs like 0.01-0.1 for sweep grids.
     */
    protected $signature = 'worldos:sweep 
                            {--n=10 : Dimension of the state space}
                            {--alpha=0.01-0.1 : Range for Damping step size alpha}
                            {--lambda=0.0 : Diffusion coefficient lambda}
                            {--eta=0.01-0.05 : Range for Intrinsic damping eta}
                            {--beta=0.1 : Input scaling beta}
                            {--steps=5 : Number of intervals per continuous parameter sweep}
                            {--ticks=1000 : Number of simulation ticks}
                            {--delta=0.05 : Target spectral margin}';

    protected $description = 'Executes parameter sweeps across the WorldOS mathematical kernel to generate stability phase diagrams';

    private SimulationRunner $runner;

    public function __construct(SimulationRunner $runner)
    {
        parent::__construct();
        $this->runner = $runner;
    }

    public function handle()
    {
        $this->info("Initializing WorldOS Stability Phase Sweep...");

        $n = (int) $this->option('n');
        $ticks = (int) $this->option('ticks');
        $deltaTarget = (float) $this->option('delta');
        $beta = (float) $this->option('beta');
        $steps = max(1, (int) $this->option('steps'));

        // Parse continuous parameters
        $alphas = $this->parseRange($this->option('alpha'), $steps);
        $lambdas = $this->parseRange($this->option('lambda'), $steps);
        $etas = $this->parseRange($this->option('eta'), $steps);

        $totalExperiments = count($alphas) * count($lambdas) * count($etas);
        $this->info("Target Experiments: {$totalExperiments}");
        
        $bar = $this->output->createProgressBar($totalExperiments);
        $bar->start();

        // Basic Initialization Method
        $initialX = array_fill(0, $n, 0.5);

        foreach ($alphas as $alpha) {
            foreach ($lambdas as $lambda) {
                foreach ($etas as $eta) {
                    $experimentId = (string) Str::uuid();

                    $config = [
                        'n_dimension' => $n,
                        'n_regions' => 1,
                        'alpha' => $alpha,
                        'lambda' => $lambda,
                        'eta' => $eta,
                        'beta' => $beta,
                        'gamma_cap' => 10.0,
                        'delta_target' => $deltaTarget,
                        'tick_count' => $ticks,
                        'kernel_version' => '1.2.0',
                        'init_method' => 'constant',
                        'precision_mode' => 'float64',
                    ];

                    // Execute deterministically without throwing exceptions up to the CLI.
                    // The SimulationRunner catches them and records as 'rejected'.
                    $this->runner->runExperiment($experimentId, $config, $initialX);
                    
                    $bar->advance();
                }
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Sweep Completed. ML Phase Diagram generated in DB.");
    }

    /**
     * Parses a single float "0.5" or a range "0.01-0.1" into an array of stepped values.
     */
    private function parseRange(string $input, int $steps): array
    {
        if (!str_contains($input, '-')) {
            return [(float) $input];
        }

        [$start, $end] = explode('-', $input, 2);
        
        $start = (float) $start;
        $end = (float) $end;
        
        if ($steps <= 1) {
            return [$start];
        }

        $stepSize = ($end - $start) / ($steps - 1);
        $values = [];

        for ($i = 0; $i < $steps; $i++) {
            $values[] = $start + ($i * $stepSize);
        }

        return $values;
    }
}
