<?php

declare(strict_types=1);

namespace App\Console\Commands;

use WorldOS\Legacy\Application\Intelligence\Services\WorldIntelligenceService;
use WorldOS\Legacy\Infrastructure\World\Repositories\WorldRepository;
use WorldOS\Legacy\Infrastructure\Character\Repositories\CharacterSurvivalRepository;
use WorldOS\Legacy\Infrastructure\World\Repositories\ShockEventRepository;
use Illuminate\Console\Command;

final class WorldIntelligenceCommand extends Command
{
    protected $signature = 'world:intelligence 
                            {--world-id= : Specific world ID to analyze}
                            {--gather : Gather new intelligence}
                            {--analyze : Show analysis and patterns}
                            {--sources : Break down by intelligence sources}
                            {--actionable : Show only actionable intelligence}
                            {--recent : Show only recent intelligence (last 10 ticks)}';

    protected $description = 'Gather and analyze world intelligence';

    public function __construct(
        private readonly WorldIntelligenceService $intelligenceService,
        private readonly WorldRepository $worldRepository,
        private readonly CharacterSurvivalRepository $characterRepository,
        private readonly ShockEventRepository $eventRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $worldId = $this->option('world-id');
        $gatherNew = $this->option('gather');
        $showAnalysis = $this->option('analyze');
        $showSources = $this->option('sources');
        $actionableOnly = $this->option('actionable');
        $recentOnly = $this->option('recent');

        if ($worldId) {
            return $this->processWorld($worldId, $gatherNew, $showAnalysis, $showSources, $actionableOnly, $recentOnly);
        } else {
            return $this->processAllWorlds($gatherNew, $showAnalysis, $showSources);
        }
    }

    private function processWorld(
        string $worldId,
        bool $gatherNew,
        bool $showAnalysis,
        bool $showSources,
        bool $actionableOnly,
        bool $recentOnly
    ): int {
        
        try {
            $world = $this->worldRepository->findById($worldId);
            
            if (!$world) {
                $this->error("World {$worldId} not found");
                return self::FAILURE;
            }

            $this->info("🕵️  Intelligence Analysis for World {$worldId}: {$world->name()}");

            // Gather new intelligence if requested
            if ($gatherNew) {
                $this->gatherIntelligence($world);
            }

            // Display intelligence
            $this->displayIntelligence($world, $showAnalysis, $showSources, $actionableOnly, $recentOnly);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Intelligence analysis failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function processAllWorlds(bool $gatherNew, bool $showAnalysis, bool $showSources): int
    {
        $worlds = $this->worldRepository->findAll();

        if ($worlds->isEmpty()) {
            $this->info('No worlds found');
            return self::SUCCESS;
        }

        $this->info("🕵️  Intelligence Analysis for All Worlds");
        $this->newLine();

        $summaryData = [];
        foreach ($worlds as $world) {
            if ($gatherNew) {
                $this->gatherIntelligence($world);
            }

            $summary = $this->getWorldIntelligenceSummary($world);
            $summaryData[] = [
                $world->id(),
                $world->name(),
                $summary['total_reports'],
                $summary['reliable_count'],
                $summary['high_urgency_count'],
                $summary['status'],
                $summary['threat_count'],
                $summary['opportunity_count'],
            ];
        }

        $this->table(
            ['ID', 'Name', 'Reports', 'Reliable', 'Urgent', 'Status', 'Threats', 'Opportunities'],
            $summaryData
        );

        if ($showAnalysis) {
            $this->displayAggregateAnalysis($worlds);
        }

        return self::SUCCESS;
    }

    private function gatherIntelligence($world): void
    {
        $this->line("🔍 Gathering intelligence for world {$world->id()}...");

        $characters = $this->characterRepository->findByWorldId($world->id());
        $activeEvents = $this->eventRepository->findActiveByWorldId($world->id());

        $collection = $this->intelligenceService->gatherIntelligence(
            $world,
            collect($characters),
            collect($activeEvents)
        );

        $this->info("✅ Gathered {$collection->count()} intelligence reports");
        $this->line("   Sources: " . implode(', ', array_keys($collection->getSourceBreakdown())));
        $this->line("   Average Accuracy: " . number_format($collection->getAverageAccuracy() * 100, 1) . '%');
    }

    private function displayIntelligence(
        $world,
        bool $showAnalysis,
        bool $showSources,
        bool $actionableOnly,
        bool $recentOnly
    ): void {
        
        $summary = $this->getWorldIntelligenceSummary($world);

        // Display summary
        $this->newLine();
        $this->info("📊 Intelligence Summary:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Reports', $summary['total_reports']],
                ['Reliable Reports', $summary['reliable_count']],
                ['Recent Reports', $summary['recent_count']],
                ['Actionable Reports', $summary['actionable_count']],
                ['High Urgency', $summary['high_urgency_count']],
                ['Average Accuracy', number_format($summary['average_accuracy'] * 100, 1) . '%'],
                ['Average Reliability', number_format($summary['average_reliability'] * 100, 1) . '%'],
                ['Overall Status', $summary['overall_assessment']['status'] ?? 'Unknown'],
            ]
        );

        // Display source breakdown
        if ($showSources) {
            $this->newLine();
            $this->info("📡 Intelligence Sources:");
            foreach ($summary['source_breakdown'] as $source => $count) {
                $this->line("  {$source}: {$count} reports");
            }
        }

        // Display analysis
        if ($showAnalysis) {
            $this->displayDetailedAnalysis($summary);
        }

        // Display actionable intelligence
        if ($actionableOnly) {
            $this->displayActionableIntelligence($world);
        }

        // Display recent intelligence
        if ($recentOnly) {
            $this->displayRecentIntelligence($world);
        }
    }

    private function displayDetailedAnalysis(array $summary): void
    {
        $this->newLine();
        $this->info("🔍 Detailed Analysis:");

        // Threats
        if (!empty($summary['threats'])) {
            $this->newLine();
            $this->warn("⚠️  Identified Threats:");
            foreach ($summary['threats'] as $threat) {
                $this->line("  • {$threat['type']}: {$threat['description']}");
                $this->line("    Severity: {$threat['severity']}");
                $this->line("    Mitigation: " . implode(', ', $threat['mitigation']));
            }
        }

        // Opportunities
        if (!empty($summary['opportunities'])) {
            $this->newLine();
            $this->info("💎 Identified Opportunities:");
            foreach ($summary['opportunities'] as $opportunity) {
                $this->line("  • {$opportunity['type']}: {$opportunity['description']}");
                $this->line("    Potential: {$opportunity['potential']}");
                $this->line("    Benefits: " . implode(', ', $opportunity['benefits']));
            }
        }

        // Recommendations
        if (!empty($summary['recommendations'])) {
            $this->newLine();
            $this->info("💡 Recommendations:");
            foreach ($summary['recommendations'] as $rec) {
                $this->line("  • {$rec}");
            }
        }

        // Overall Assessment
        if (!empty($summary['overall_assessment'])) {
            $this->newLine();
            $this->info("🎯 Overall Assessment:");
            $assessment = $summary['overall_assessment'];
            $this->line("  Status: {$assessment['status']}");
            $this->line("  Score: " . number_format($assessment['score'] ?? 0, 1));
            $this->line("  Recommendation: " . ($assessment['recommendation'] ?? 'N/A'));
        }
    }

    private function displayActionableIntelligence($world): void
    {
        $this->newLine();
        $this->info("⚡ Actionable Intelligence:");
        
        // This would fetch actionable intelligence from storage
        // For now, showing placeholder
        $this->line("  • High entropy detected - implement stabilization measures");
        $this->line("  • Faction instability detected - diplomatic intervention recommended");
        $this->line("  • Resource scarcity identified - rationing protocols advised");
    }

    private function displayRecentIntelligence($world): void
    {
        $this->newLine();
        $this->info("🕐 Recent Intelligence (Last 10 Ticks):");
        
        // This would fetch recent intelligence from storage
        // For now, showing placeholder
        $this->line("  • Character vulnerability patterns detected");
        $this->line("  • Environmental stress indicators rising");
        $this->line("  • Faction movements observed");
        $this->line("  • Myth activity fluctuations noted");
    }

    private function getWorldIntelligenceSummary($world): array
    {
        // This would fetch actual intelligence from storage
        // For now, returning mock data
        return [
            'total_reports' => rand(20, 50),
            'reliable_count' => rand(10, 30),
            'recent_count' => rand(5, 15),
            'actionable_count' => rand(3, 10),
            'high_urgency_count' => rand(1, 5),
            'average_accuracy' => rand(60, 90) / 100,
            'average_reliability' => rand(50, 80) / 100,
            'source_breakdown' => [
                'character' => rand(5, 15),
                'environment' => rand(3, 8),
                'event' => rand(2, 6),
                'faction' => rand(3, 7),
                'myth' => rand(1, 4),
            ],
            'threats' => [
                [
                    'type' => 'entropy_crisis',
                    'description' => 'World entropy approaching critical levels',
                    'severity' => 'high',
                    'mitigation' => ['reduce_conflicts', 'stabilize_factions']
                ]
            ],
            'opportunities' => [
                [
                    'type' => 'alliance_formation',
                    'description' => 'Stable factions could form alliances',
                    'potential' => 'high',
                    'benefits' => ['stability_increase', 'resource_sharing']
                ]
            ],
            'recommendations' => [
                'Focus on threat mitigation - immediate dangers outweigh opportunities',
                'Increase defensive measures and emergency preparedness'
            ],
            'overall_assessment' => [
                'status' => 'cautious',
                'score' => 15.5,
                'recommendation' => 'Increase vigilance and prepare contingency plans'
            ]
        ];
    }

    private function displayAggregateAnalysis($worlds): void
    {
        $this->newLine();
        $this->info("🌍 Aggregate Intelligence Analysis:");
        
        $totalWorlds = $worlds->count();
        $this->line("  Total Worlds Analyzed: {$totalWorlds}");
        
        // Calculate aggregate metrics
        $avgReports = 35; // Mock calculation
        $avgThreats = 1.2;
        $avgOpportunities = 0.8;
        
        $this->line("  Average Reports per World: {$avgReports}");
        $this->line("  Average Threats per World: {$avgThreats}");
        $this->line("  Average Opportunities per World: {$avgOpportunities}");
        
        $this->newLine();
        $this->info("🎯 Global Recommendations:");
        $this->line("  • Monitor worlds with high threat levels");
        $this->line("  • Facilitate opportunity sharing between stable worlds");
        $this->line("  • Implement cross-world intelligence sharing protocols");
    }
}
