<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Application\Evolution\Dynamics\DriftField;
use Tuzy\Application\Evolution\Engine\VectorDynamicsEngine;
use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObjects\EvolutionResult;
use Illuminate\Support\Str;

class WorldV3TestCommand extends Command
{
    protected $signature = 'world:v3-test 
        {--ticks=100 : Số lượng ticks mô phỏng} 
        {--order=0.5} 
        {--entropy=0.3} 
        {--cohesion=0.5} 
        {--legitimacy=0.8} 
        {--innovation=0.1} 
        {--military=0.1}
        {--inequality=0.2}
        {--trauma=0.0}
        {--elite_cohesion=0.7}
        {--resource_stock=0.5}';
    protected $description = 'Kiểm thử vũ trụ V3 sử dụng thuật toán Vector Dynamics nguyên bản (tanh integration, 10 dimensions, drift field)';

    public function handle()
    {
        $ticks = (int) $this->option('ticks');
        $this->info("Bắt đầu thử nghiệm Vũ trụ V3: $ticks Ticks");

        // 1. Khởi tạo State ban đầu
        $initialVector = WorldStateVector::create(
            (float) $this->option('order'),
            (float) $this->option('entropy'),
            (float) $this->option('cohesion'),
            (float) $this->option('legitimacy'),
            (float) $this->option('innovation'),
            (float) $this->option('military'),
            (float) $this->option('inequality'),
            (float) $this->option('trauma'),
            (float) $this->option('elite_cohesion'),
            (float) $this->option('resource_stock')
        );

        // 2. Setup Engines
        $driftField = new DriftField();
        $engine = new VectorDynamicsEngine($driftField);
        $context = new EvolutionContext(Str::uuid()->toString(), 0, 'v3_test');

        // 3. Vòng lặp mô phỏng
        $currentState = $initialVector;
        $prevState = null;

        $bar = $this->output->createProgressBar($ticks);
        $bar->start();

        for ($i = 1; $i <= $ticks; $i++) {
            $result = $engine->step($currentState, $context, $prevState);
            
            $prevState = $currentState;
            $currentState = $result->nextState;
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // 4. Báo cáo kết quả
        $this->info("--- KẾT QUẢ VŨ TRỤ V3 (SAU $ticks TICKS) ---");
        $dims = WorldStateVector::dimensions();
        foreach ($dims as $dim) {
            $val = $currentState->get($dim);
            $this->line(sprintf(" - %-20s: %.4f", ucfirst($dim), $val));
        }

        $this->newLine();
        $this->info("Độ phân kỳ (Divergence): " . number_format($currentState->divergence(), 4));
        $this->info("Độ cong cuối cùng (Curvature): " . ($prevState ? number_format($currentState->curvature($prevState), 6) : 'N/A'));
        
        $this->newLine();
    }
}
