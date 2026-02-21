<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Tuzy\Application\World\Services\WorldLifecycleAnalyzer;
use Tuzy\Infrastructure\World\Repositories\WorldRepository;
use Tuzy\Infrastructure\Character\Repositories\CharacterSurvivalRepository;
use Illuminate\Console\Command;

final class WorldLifecycleCommand extends Command
{
    protected $signature = 'world:lifecycle 
                            {--world-id= : Specific world ID to analyze}
                            {--predict : Show long-term predictions}
                            {--compare : Compare with human civilizations}
                            {--detailed : Show detailed analysis}';

    protected $description = 'Analyze world lifecycle and predict longevity';

    public function __construct(
        private readonly WorldLifecycleAnalyzer $lifecycleAnalyzer,
        private readonly WorldRepository $worldRepository,
        private readonly CharacterSurvivalRepository $characterRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $worldId = $this->option('world-id');
        $showPredictions = $this->option('predict');
        $showComparison = $this->option('compare');
        $detailed = $this->option('detailed');

        if ($worldId) {
            return $this->analyzeSingleWorld($worldId, $showPredictions, $showComparison, $detailed);
        } else {
            return $this->analyzeAllWorlds($showComparison, $detailed);
        }
    }

    private function analyzeSingleWorld(
        string $worldId, 
        bool $showPredictions, 
        bool $showComparison, 
        bool $detailed
    ): int {
        
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                $this->error("World {$worldId} not found");
                return self::FAILURE;
            }

            $characters = $this->characterRepository->findByWorldId($worldId);
            $report = $this->lifecycleAnalyzer->analyzeLifecycle($world, collect($characters));

            $this->displayWorldReport($report, $detailed);

            if ($showPredictions) {
                $this->displayPredictions($world, collect($characters));
            }

            if ($showComparison) {
                $this->displayHumanComparison($report);
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Analysis failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function analyzeAllWorlds(bool $showComparison, bool $detailed): int
    {
        $worlds = $this->worldRepository->findAll();

        if ($worlds->isEmpty()) {
            $this->info('No worlds found');
            return self::SUCCESS;
        }

        $this->info("🌍 WORLD LIFECYCLE ANALYSIS");
        $this->newLine();

        $summaryData = [];
        foreach ($worlds as $world) {
            $characters = $this->characterRepository->findByWorldId($world->id());
            $report = $this->lifecycleAnalyzer->analyzeLifecycle($world, collect($characters));

            $summaryData[] = [
                $world->id(),
                $world->name(),
                $report->currentAge,
                $report->lifecyclePhase->getDisplayName(),
                number_format($report->remainingLifetime, 0),
                number_format($report->totalPredictedLifetime, 0),
                $report->isLongLived() ? '✅' : '❌',
                $report->isNearCollapse() ? '⚠️' : '✅',
            ];

            if ($detailed) {
                $this->newLine();
                $this->line("📊 World {$world->id()}: {$world->name()}");
                $this->displayWorldReport($report, false);
            }
        }

        $this->newLine();
        $this->table(
            ['ID', 'Name', 'Age', 'Phase', 'Remaining', 'Total', 'Long-lived', 'Status'],
            $summaryData
        );

        if ($showComparison) {
            $this->displayAggregateComparison($summaryData);
        }

        return self::SUCCESS;
    }

    private function displayWorldReport($report, bool $detailed): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Current Age', "{$report->currentAge} years"],
                ['Lifecycle Phase', $report->lifecyclePhase->getDisplayName()],
                ['Remaining Lifetime', "{$report->remainingLifetime} years"],
                ['Total Predicted', "{$report->totalPredictedLifetime} years"],
                ['Stability Score', number_format($report->stabilityScore, 3)],
                ['Collapse Risk', number_format($report->collapseRisk, 3)],
                ['Character Survival', number_format($report->characterSurvivalRate * 100, 1) . '%'],
                ['Lifecycle Efficiency', number_format($report->getLifecycleEfficiency() * 100, 1) . '%'],
                ['Long-lived', $report->isLongLived() ? '✅ Yes' : '❌ No'],
                ['Near Collapse', $report->isNearCollapse() ? '⚠️ Yes' : '✅ No'],
            ]
        );

        if (!empty($report->recommendations)) {
            $this->newLine();
            $this->info('💡 Recommendations:');
            foreach ($report->recommendations as $rec) {
                $this->line("  • {$rec}");
            }
        }

        if ($detailed) {
            $this->newLine();
            $this->displayDetailedMetrics($report);
        }
    }

    private function displayDetailedMetrics($report): void
    {
        $this->info('📈 Detailed Analysis:');
        
        // Lifecycle efficiency visualization
        $efficiency = $report->getLifecycleEfficiency();
        $bar = str_repeat('█', (int)($efficiency * 20));
        $this->line("  Lifecycle Efficiency: [{$bar}] " . number_format($efficiency * 100, 1) . '%');

        // Risk assessment
        $risk = $report->collapseRisk;
        $riskBar = str_repeat('🔴', (int)($risk * 10));
        $this->line("  Collapse Risk:        [{$riskBar}] " . number_format($risk * 100, 1) . '%');

        // Stability assessment
        $stability = $report->stabilityScore;
        $stabBar = str_repeat('🟢', (int)($stability * 10));
        $this->line("  Stability:            [{$stabBar}] " . number_format($stability * 100, 1) . '%');

        // Age comparison
        $agePercent = ($report->currentAge / 500) * 100; // 500 years average
        $ageBar = str_repeat('📅', (int)($agePercent / 5));
        $this->line("  Age Progress:         [{$ageBar}] " . number_format($agePercent, 1) . '% of average');
    }

    private function displayPredictions($world, $characters): void
    {
        $predictions = $this->lifecycleAnalyzer->predictLongTermEvolution($world, collect($characters));

        $this->newLine();
        $this->info('🔮 Long-term Predictions:');
        
        $predictionData = [];
        foreach ($predictions as $pred) {
            $riskIcon = match ($pred['risk_level']) {
                'critical' => '🔴',
                'high' => '🟠',
                'moderate' => '🟡',
                default => '🟢'
            };

            $predictionData[] = [
                $pred['tick'],
                $pred['year'],
                number_format($pred['entropy'], 3),
                $pred['phase'],
                $riskIcon . ' ' . ucfirst($pred['risk_level'])
            ];
        }

        $this->table(
            ['Tick', 'Year', 'Entropy', 'Phase', 'Risk'],
            $predictionData
        );
    }

    private function displayHumanComparison($report): void
    {
        $comparison = $report->humanComparison;

        $this->newLine();
        $this->info('👥 Human Civilization Comparison:');
        
        $this->table(
            ['Aspect', 'World', 'Human Average', 'Status'],
            [
                ['Age', "{$comparison->worldAge} years", "{$comparison->humanEquivalentAge} years", 
                 $comparison->isLongerThanAverage ? '✅ Longer' : '⏰ Average'],
                ['Phase', $comparison->actualPhase->getDisplayName(), $comparison->expectedPhase->getDisplayName(),
                 $comparison->phaseAlignment === 'aligned' ? '✅ Aligned' : '⚠️ Misaligned'],
                ['Stability', ucfirst($comparison->stabilityComparison), 'Moderately Stable',
                 $comparison->stabilityComparison === 'moderately_stable' ? '✅ Similar' : '📊 Different'],
                ['Lifecycle', ucfirst($comparison->lifecycleComparison), 'Standard Lifecycle',
                 $comparison->isLongerThanAverage ? '✅ Extended' : '📊 Normal'],
            ]
        );

        // Interpretation
        $this->newLine();
        $this->info('📊 Interpretation:');
        
        if ($comparison->phaseAlignment === 'aligned') {
            $this->line('  ✅ World follows natural civilization patterns');
        } else {
            $this->line('  ⚠️ World development differs from human patterns');
        }

        if ($comparison->isLongerThanAverage) {
            $this->line('  🌟 This world shows exceptional longevity');
        }

        if ($comparison->stabilityComparison === 'highly_stable') {
            $this->line('  🛡️ Unusual stability - may indicate stagnation');
        }
    }

    private function displayAggregateComparison(array $summaryData): void
    {
        $this->newLine();
        $this->info('🌍 Aggregate World Analysis:');
        
        $totalWorlds = count($summaryData);
        $longLived = count(array_filter($summaryData, fn($row) => $row[6] === '✅'));
        $nearCollapse = count(array_filter($summaryData, fn($row) => $row[7] === '⚠️'));
        
        $this->line("  Total Worlds: {$totalWorlds}");
        $this->line("  Long-lived Worlds: {$longLived}/{$totalWorlds} (" . number_format(($longLived/$totalWorlds)*100, 1) . '%)');
        $this->line("  Near Collapse: {$nearCollapse}/{$totalWorlds} (" . number_format(($nearCollapse/$totalWorlds)*100, 1) . '%)');
        
        if ($longLived > $totalWorlds * 0.5) {
            $this->line("  🌟 Your worlds tend to be longer-lived than human civilizations");
        } elseif ($nearCollapse > $totalWorlds * 0.3) {
            $this->line("  ⚠️ High collapse rate - consider adjusting entropy parameters");
        } else {
            $this->line("  ✅ Worlds show balanced lifecycle patterns");
        }
    }
}
