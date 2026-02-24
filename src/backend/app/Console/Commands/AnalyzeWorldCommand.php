<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\WorldOS\Governance\Services\DecisionEngine;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use Illuminate\Console\Command;

/**
 * Artisan command: Analyze a Universe and display governance metrics.
 *
 * From docs §13.3: php artisan world:analyze {world_id}
 */
class AnalyzeWorldCommand extends Command
{
    protected $signature = 'world:analyze {universe_id : UUID of the Universe to analyze}';

    protected $description = 'Analyze a Universe and display governance metrics + recommendation';

    public function handle(DecisionEngine $engine): int
    {
        $universeId = new UniverseId($this->argument('universe_id'));

        $this->info("🔍 Analyzing Universe: {$universeId->value}");
        $this->newLine();

        $result = $engine->evaluateOnly($universeId);

        if (!$result['metrics'] || !$result['result']) {
            $this->error("Universe not found: {$universeId->value}");

            return self::FAILURE;
        }

        $metrics = $result['metrics'];
        $evaluation = $result['result'];

        // Display metrics
        $this->info('📊 Universe Metrics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Entropy Trend', $this->formatTrend($metrics->entropyTrend)],
                ['Complexity Index', $this->formatPercent($metrics->complexityIndex)],
                ['Stability Score', $this->formatPercent($metrics->stabilityScore)],
                ['Collapse Risk', $this->formatRisk($metrics->collapseRisk)],
                ['Innovation Trend', $this->formatTrend($metrics->innovationTrend)],
                ['IP Score', $this->formatPercent($metrics->ipScore)],
                ['Ticks Analyzed', (string) $metrics->ticksAnalyzed],
            ]
        );

        // Display evaluation
        $this->newLine();
        $this->info('🏛️ Governance Evaluation:');
        $recommendationLabel = match ($evaluation->recommendation) {
            'continue' => '🟢 CONTINUE',
            'fork' => '🔀 FORK',
            'archive' => '📦 ARCHIVE',
            default => $evaluation->recommendation,
        };

        $this->line("  Recommendation: {$recommendationLabel}");
        $this->line("  Confidence: " . round($evaluation->confidence * 100) . '%');
        $this->line("  Reasoning: {$evaluation->reasoning}");

        if ($evaluation->mutationSuggestion) {
            $this->line("  Mutation: {$evaluation->mutationSuggestion}");
        }

        // Quick assessment
        $this->newLine();
        if ($metrics->isInteresting()) {
            $this->info('⭐ This universe has high IP potential!');
        }
        if ($metrics->isAtRisk()) {
            $this->warn('⚠️  This universe is at risk of collapse.');
        }
        if ($metrics->isStagnant()) {
            $this->warn('💤 This universe is stagnant.');
        }

        return self::SUCCESS;
    }

    private function formatTrend(float $value): string
    {
        $arrow = $value > 0.05 ? '↑' : ($value < -0.05 ? '↓' : '→');

        return sprintf('%s %.2f', $arrow, $value);
    }

    private function formatPercent(float $value): string
    {
        return sprintf('%.1f%%', $value * 100);
    }

    private function formatRisk(float $value): string
    {
        $label = match (true) {
            $value >= 0.7 => '🔴 HIGH',
            $value >= 0.4 => '🟡 MEDIUM',
            default => '🟢 LOW',
        };

        return sprintf('%s (%.2f)', $label, $value);
    }
}
