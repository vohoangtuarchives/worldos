<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use WorldOS\Applications\Simulator\StepWorldUseCase;
use WorldOS\Infrastructure\EventBus\EventBus;
use WorldOS\Domains\Evolution\WorldStateRepository;
use WorldOS\Domains\Evolution\CivilizationStateRepository;
use WorldOS\Domains\Evolution\WorldState;
use WorldOS\Domains\Evolution\CivilizationState;
use Illuminate\Support\Str;
use WorldOS\Infrastructure\Persistence\Evolution\InMemoryWorldStateRepository;
use WorldOS\Infrastructure\Persistence\Evolution\InMemoryCivilizationStateRepository;
use WorldOS\Infrastructure\EventBus\LaravelEventBus;
use WorldOS\Infrastructure\Persistence\Cosmology\InMemoryWorldRepository;
use WorldOS\Domains\Cosmology\Contracts\WorldRepositoryInterface;
use WorldOS\Domains\Cosmology\WorldRepository;
use WorldOS\Domains\Evolution\Enums\CivilizationLifecycleState;

class WorldBlindRunCommand extends Command
{
    protected $signature = 'world:blind-run 
        {--ticks=500 : Số lượng ticks mô phỏng} 
        {--delta=1 : Số năm mỗi tick}
        {--ce=0.15 : Cultural Energy}
        {--sc=0.20 : Spiritual Cohesion}
        {--tech=0.10 : Technological Level}
        {--stability=0.60 : Stability}
        {--prosperity=0.30 : Prosperity}
        {--mp=0.10 : Military Pressure}
        {--et=0.05 : External Threat}
        {--ie=0.10 : Internal Entropy}
        {--legitimacy=0.80 : Legitimacy}
        {--elite_cohesion=0.70 : Elite Cohesion}
        {--inequality=0.30 : Inequality}
        {--stop-on-extinction : Ngắt mô phỏng khi tuyệt chủng}';
    protected $description = 'Chạy mô phỏng (Blind Run) 1 vạn năm không can thiệp để test hệ thống Event Cascade và Drift của V3 Core';

    public function handle()
    {
        $ticks = (int) $this->option('ticks');
        $delta = (int) $this->option('delta');
        $totalYears = $ticks * $delta;
        
        $this->info("Bắt đầu thử nghiệm Blind Run: $ticks Ticks ($totalYears Năm)");

        // 1. Khởi tạo In-Memory Repositories cho test độc lập siêu tốc
        $worldStateRepo = new InMemoryWorldStateRepository();
        $civStateRepo = new InMemoryCivilizationStateRepository();
        
        // 2. Register vào Container để Laravel tự động inject vào UseCase chung với Pipeline
        app()->instance(WorldStateRepository::class, $worldStateRepo);
        app()->instance(CivilizationStateRepository::class, $civStateRepo);
        app()->instance(WorldRepositoryInterface::class, new InMemoryWorldRepository());
        app()->instance(WorldRepository::class, new InMemoryWorldRepository());
        app()->instance(\WorldOS\Domains\Evolution\Contracts\AttractorRepositoryInterface::class, new \WorldOS\Infrastructure\Persistence\Evolution\InMemoryAttractorRepository());
        if (!app()->bound(EventBus::class)) {
            app()->instance(EventBus::class, new LaravelEventBus(app('events')));
        }

        // 3. Khởi tạo Material Domain cho Test
        $materialRegistry = new \WorldOS\Domains\Material\MaterialRegistry();
        $materialService = new \WorldOS\Domains\Material\Services\MaterialEvolutionService($materialRegistry);
        app()->instance(\WorldOS\Domains\Material\MaterialRegistry::class, $materialRegistry);
        app()->instance(\WorldOS\Domains\Material\Services\MaterialEvolutionService::class, $materialService);
        app()->instance(\WorldOS\Domains\Evolution\Contracts\EntropyLedgerInterface::class, new \WorldOS\Infrastructure\Persistence\Evolution\InMemoryEntropyLedger());

        // Thêm một Faction mẫu
        $sampleFaction = new \WorldOS\Domains\Material\Faction(Str::uuid()->toString(), "Đại Việt Đế Quốc", "empire", 1.0);
        $materialRegistry->addFaction($sampleFaction);
        
        // 4. Khởi tạo UseCase thông qua Container
        /** @var StepWorldUseCase $useCase */
        $useCase = app(StepWorldUseCase::class);

        // 4. Tạo dữ liệu giả định ban đầu (World Genesis)
        $worldId = Str::uuid()->toString();
        $this->info("Khởi tạo Thế giới thử nghiệm ID: $worldId");
        
        $initialState = new WorldState($worldId, null, null, 0); // CosmicState, EnvironmentState sẽ mặc định create Year 0
        $worldStateRepo->save($initialState);

        $initialSnapshot = new \WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot(
            culturalEnergy: (float) $this->option('ce'),
            spiritualCohesion: (float) $this->option('sc'),
            technologicalLevel: (float) $this->option('tech'),
            stability: (float) $this->option('stability'),
            prosperity: (float) $this->option('prosperity'),
            militaryPressure: (float) $this->option('mp'),
            externalThreat: (float) $this->option('et'),
            internalEntropy: (float) $this->option('ie'),
            legitimacy: (float) $this->option('legitimacy'),
            eliteCohesion: (float) $this->option('elite_cohesion'),
            inequality: (float) $this->option('inequality'),
            resonanceAccumulator: 0.0,
            resilience: 1.0,
            year: 0
        );

        $civId = Str::uuid()->toString();
        $civState = new CivilizationState($civId, $worldId, null, $initialSnapshot);
        $civStateRepo->save($civState);
        $this->info("Khởi tạo Văn minh đầu tiên ID: $civId");

        // 5. Bắt đầu vòng lặp thời gian
        $bar = $this->output->createProgressBar($ticks);
        $bar->start();

        $allLogs = [];
        $startTime = microtime(true);

        for ($i = 1; $i <= $ticks; $i++) {
            $logs = $useCase->execute($worldId, 1, $delta);
            if (!empty($logs)) {
                $allLogs[$i] = $logs;
            }
            $bar->advance();

            // Check for extinction if stop-on-extinction is enabled
            if ($this->option('stop-on-extinction')) {
                $civState = $civStateRepo->findById($civId);
                if ($civState && $civState->getSnapshot()->lifecycleState === CivilizationLifecycleState::EXTINCT) {
                    $this->newLine();
                    $this->error("VĂN MINH ĐÃ TUYỆT CHỦNG TẠI TICK $i - DỪNG MÔ PHỎNG.");
                    break;
                }
            }
        }

        $bar->finish();
        $endTime = microtime(true);
        $duration = number_format($endTime - $startTime, 2);

        $this->newLine(2);
        $this->info("Mô phỏng hoàn tất trong {$duration} giây. Nhận báo cáo:");

        $finalWorldState = $worldStateRepo->findById($worldId);
        $finalCivState = $civStateRepo->findById($civId);

        $this->info("--- THÔNG SỐ VŨ TRỤ (COSMIC) ---");
        $this->line(" - Order: " . number_format($finalWorldState->getCosmicState()->order, 4));
        $this->line(" - Entropy: " . number_format($finalWorldState->getEntropy(), 4));
        $this->line(" - Energy: " . number_format($finalWorldState->getCosmicState()->energy, 4));
        $this->line(" - Causality: " . number_format($finalWorldState->getCosmicState()->causality, 4));
        $this->line(" - Strain: " . number_format($finalWorldState->getCosmicState()->strain, 4));
        $this->line(" - Stability: " . number_format($finalWorldState->getCosmicState()->stability, 4));
        $this->line(" - Current Attractor: " . $finalWorldState->getCosmicState()->currentAttractor);
        $this->newLine();
        $this->info("--- THÔNG SỐ THẾ GIỚI (WORLD LAYER) ---");
        $this->line(" - World Phase: " . $finalWorldState->getWorldPhase()->label());
        $this->line(" - Life State:  " . $finalWorldState->getLifeState()->getBiodiversityLabel() . " (Complexity: " . number_format($finalWorldState->getLifeState()->complexity, 4) . ")");
        $this->line("Năm mô phỏng dừng lại: " . $finalWorldState->getYear());
        
        if ($finalCivState) {
            $snap = $finalCivState->getSnapshot();
            $this->newLine();
            $this->info("--- THÔNG SỐ VĂN MINH (17 DIMENSIONS) ---");
            $this->line(sprintf(" 1. Cultural Energy:    %-8s | 10. Internal Entropy:   %-8s", number_format($snap->culturalEnergy, 4), number_format($snap->internalEntropy, 4)));
            $this->line(sprintf(" 2. Spiritual Cohesion: %-8s | 11. Field Curvature:    %-8s", number_format($snap->spiritualCohesion, 4), number_format($snap->fieldCurvature, 4)));
            $this->line(sprintf(" 3. Technological Level:%-8s | 12. Sustainability:     %-8s", number_format($snap->technologicalLevel, 4), number_format($snap->sustainability, 4)));
            $this->line(sprintf(" 4. Stability:          %-8s | 13. Mystery/Arcane:     %-8s", number_format($snap->stability, 4), number_format($snap->mystery, 4)));
            $this->line(sprintf(" 5. Prosperity:         %-8s | 14. Historical Legacy:  %-8s", number_format($snap->prosperity, 4), number_format($snap->historicalLegacy, 4)));
            $this->line(sprintf(" 6. Military Pressure:  %-8s | 15. Expansionism:       %-8s", number_format($snap->militaryPressure, 4), number_format($snap->expansionism, 4)));
            $this->line(sprintf(" 7. Legitimacy:         %-8s | 16. Information Flow:   %-8s", number_format($snap->legitimacy, 4), number_format($snap->informationFlow, 4)));
            $this->line(sprintf(" 8. Elite Cohesion:     %-8s | 17. Social Mobility:    %-8s", number_format($snap->eliteCohesion, 4), number_format($snap->socialMobility, 4)));
            $this->line(sprintf(" 9. Inequality:         %-8s", number_format($snap->inequality, 4)));
            $this->newLine();
            $this->newLine();
            $this->info(" >>> HISTORY PHASE: " . $snap->historyPhase->label() . " <<< ");
            $this->warn(" >>> POWER STAGE:   " . $snap->powerStage->label() . " <<< ");
            $this->error(" >>> LIFECYCLE:     " . strtoupper($snap->lifecycleState->value) . " <<< ");
            $this->newLine();
            $this->line(" --- NARRATIVE ---");
            $this->line(sprintf(" - Tension (Short|Long): %-8s | %-8s", number_format($snap->shortWaveTension, 4), number_format($snap->longWaveTension, 4)));
            $this->line(sprintf(" - Cumulative Trauma:    %-8s", number_format($snap->getResidual()->cumulativeTrauma, 4)));
            $this->line(sprintf(" - Total Narrative Var:  %-8s", number_format($snap->narrativeTension, 4)));
            $this->line(sprintf(" - Hero Count:           %-8s", $snap->heroCount));
            $this->newLine();
            $this->line("Resilience cuối cùng: " . number_format($snap->resilience, 4));
        }

        $this->newLine();
        $this->info("--- THÔNG SỐ VẬT CHẤT (MATERIAL) ---");
        $registry = app(\WorldOS\Domains\Material\MaterialRegistry::class);
        foreach ($registry->getAllFactions() as $faction) {
            $this->line(" - Faction [{$faction->getName()}]: Power " . number_format($faction->getPowerLevel(), 4));
        }

        $this->line("Tổng số sự kiện/biến động đã ghi nhận: " . count($allLogs));

        if ($this->option('verbose')) {
            $this->newLine();
            $this->info('--- CHI TIẾT SỰ KIỆN ---');
            foreach ($allLogs as $tick => $logsArr) {
                $year = $tick * $delta;
                $this->warn("TICK $tick (Năm $year):");
                foreach ($logsArr as $log) {
                    $this->line(" - $log");
                }
            }
        }
        
        $this->newLine();
    }
}
