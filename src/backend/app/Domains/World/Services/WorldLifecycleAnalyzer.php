<?php

declare(strict_types=1);

namespace App\Domains\World\Services;

use App\Domains\World\Aggregates\WorldAggregate;
use Tuzy\Domain\World\ValueObject\EntropyScore;
use App\Domains\Character\Aggregates\CharacterSurvivalAggregate;
use Illuminate\Support\Collection;

final class WorldLifecycleAnalyzer
{
    // Human civilization benchmarks
    private const HUMAN_CIVILIZATION_AVERAGES = [
        'rise_time' => 200,        // years to rise
        'peak_time' => 100,        // years at peak
        'decline_time' => 150,     // years to decline
        'collapse_time' => 50,     // years to collapse
        'total_lifecycle' => 500,  // total years
    ];

    // World tick to human time conversion (configurable)
    private const TICK_TO_YEARS_RATIO = 5; // 1 tick = 5 years

    public function analyzeLifecycle(WorldAggregate $world, Collection $characters): WorldLifecycleReport
    {
        $currentTick = $world->currentTick();
        $currentEntropy = $world->currentEntropy();
        $characterCount = $characters->count();
        $aliveCount = $characters->filter(fn($c) => $c->isAlive())->count();

        // Calculate lifecycle phase
        $phase = $this->determineLifecyclePhase($currentTick, $currentEntropy, $aliveCount, $characterCount);

        // Predict remaining lifetime
        $remainingTicks = $this->predictRemainingLifetime($world, $characters);
        $remainingYears = $remainingTicks * self::TICK_TO_YEARS_RATIO;

        // Calculate civilization metrics
        $civilizationAge = $currentTick * self::TICK_TO_YEARS_RATIO;
        $stabilityScore = $this->calculateStabilityScore($world, $characters);
        $collapseRisk = $this->calculateCollapseRisk($world, $characters);

        // Compare with human benchmarks
        $humanComparison = $this->compareWithHumanCivilization($civilizationAge, $phase, $stabilityScore);

        return new WorldLifecycleReport(
            worldId: $world->id(),
            currentTick: $currentTick,
            currentAge: $civilizationAge,
            lifecyclePhase: $phase,
            remainingLifetime: $remainingYears,
            totalPredictedLifetime: $civilizationAge + $remainingYears,
            stabilityScore: $stabilityScore,
            collapseRisk: $collapseRisk,
            characterSurvivalRate: $characterCount > 0 ? $aliveCount / $characterCount : 0,
            humanComparison: $humanComparison,
            recommendations: $this->generateRecommendations($phase, $collapseRisk, $stabilityScore)
        );
    }

    private function determineLifecyclePhase(
        int $tick,
        EntropyScore $entropy,
        int $aliveCount,
        int $totalCharacters
    ): LifecyclePhase {
        
        $survivalRate = $totalCharacters > 0 ? $aliveCount / $totalCharacters : 0;
        $entropyValue = $entropy->value();

        // Phase determination based on multiple factors
        if ($tick < 20 && $entropyValue < 0.3 && $survivalRate > 0.8) {
            return LifecyclePhase::RISING;
        }

        if ($tick >= 20 && $tick < 50 && $entropyValue < 0.5 && $survivalRate > 0.6) {
            return LifecyclePhase::PEAK;
        }

        if ($entropyValue >= 0.5 && $entropyValue < 0.8 && $survivalRate > 0.3) {
            return LifecyclePhase::DECLINING;
        }

        if ($entropyValue >= 0.8 || $survivalRate <= 0.3) {
            return LifecyclePhase::COLLAPSING;
        }

        if ($entropyValue < 0.2 && $survivalRate > 0.9) {
            return LifecyclePhase::STAGNANT;
        }

        return LifecyclePhase::MATURE;
    }

    private function predictRemainingLifetime(WorldAggregate $world, Collection $characters): int
    {
        $currentEntropy = $world->currentEntropy()->value();
        $entropyGrowthRate = $this->calculateEntropyGrowthRate($world);
        
        // Base prediction on entropy trajectory
        if ($currentEntropy >= 0.9) {
            return rand(1, 5); // Imminent collapse
        }

        if ($currentEntropy >= 0.7) {
            // High entropy - rapid decline
            $ticksToCollapse = (int) ((0.95 - $currentEntropy) / $entropyGrowthRate);
            return max(1, min($ticksToCollapse, 20));
        }

        if ($currentEntropy >= 0.5) {
            // Medium entropy - gradual decline
            $ticksToCollapse = (int) ((0.9 - $currentEntropy) / $entropyGrowthRate);
            return max(10, min($ticksToCollapse, 50));
        }

        // Low entropy - stable period
        $stablePeriod = rand(30, 80);
        $declinePeriod = rand(20, 40);
        
        return $stablePeriod + $declinePeriod;
    }

    private function calculateEntropyGrowthRate(WorldAggregate $world): float
    {
        $baseRate = 0.02; // Base entropy increase per tick
        
        // Adjust based on world state
        $entropyMultiplier = 1 + $world->currentEntropy()->value();
        
        return $baseRate * $entropyMultiplier;
    }

    private function calculateStabilityScore(WorldAggregate $world, Collection $characters): float
    {
        $entropy = $world->currentEntropy()->value();
        $survivalRate = $characters->isEmpty() ? 1.0 : 
            $characters->filter(fn($c) => $c->isAlive())->count() / $characters->count();

        // Stability factors
        $entropyStability = 1.0 - $entropy;
        $populationStability = $survivalRate;
        $tickStability = $world->currentTick() > 100 ? 0.8 : 1.0; // Older worlds less stable

        return ($entropyStability * 0.5) + ($populationStability * 0.3) + ($tickStability * 0.2);
    }

    private function calculateCollapseRisk(WorldAggregate $world, Collection $characters): float
    {
        $entropy = $world->currentEntropy()->value();
        $survivalRate = $characters->isEmpty() ? 1.0 : 
            $characters->filter(fn($c) => $c->isAlive())->count() / $characters->count();

        // Risk factors
        $entropyRisk = $entropy * 0.6;
        $populationRisk = (1.0 - $survivalRate) * 0.3;
        $ageRisk = $world->currentTick() > 200 ? 0.1 : 0.0;

        return min(1.0, $entropyRisk + $populationRisk + $ageRisk);
    }

    private function compareWithHumanCivilization(
        int $worldAge,
        LifecyclePhase $phase,
        float $stabilityScore
    ): HumanCivilizationComparison {
        
        $humanAge = $worldAge;
        $expectedHumanPhase = $this->getExpectedHumanPhase($humanAge);
        
        $phaseAlignment = $expectedHumanPhase === $phase ? 'aligned' : 'misaligned';
        $stabilityComparison = $this->compareStability($stabilityScore);
        
        $lifecycleComparison = match (true) {
            $worldAge < 200 => 'early_stage',
            $worldAge < 400 => 'mid_stage', 
            $worldAge < 600 => 'late_stage',
            default => 'extended_lifecycle'
        };

        return new HumanCivilizationComparison(
            worldAge: $worldAge,
            humanEquivalentAge: $humanAge,
            expectedPhase: $expectedHumanPhase,
            actualPhase: $phase,
            phaseAlignment: $phaseAlignment,
            stabilityComparison: $stabilityComparison,
            lifecycleComparison: $lifecycleComparison,
            isLongerThanAverage: $worldAge > self::HUMAN_CIVILIZATION_AVERAGES['total_lifecycle']
        );
    }

    private function getExpectedHumanPhase(int $age): LifecyclePhase
    {
        if ($age < 200) return LifecyclePhase::RISING;
        if ($age < 300) return LifecyclePhase::PEAK;
        if ($age < 450) return LifecyclePhase::DECLINING;
        if ($age < 500) return LifecyclePhase::COLLAPSING;
        return LifecyclePhase::COLLAPSED;
    }

    private function compareStability(float $worldStability): string
    {
        if ($worldStability > 0.8) return 'highly_stable';
        if ($worldStability > 0.6) return 'moderately_stable';
        if ($worldStability > 0.4) return 'unstable';
        return 'highly_unstable';
    }

    private function generateRecommendations(
        LifecyclePhase $phase,
        float $collapseRisk,
        float $stabilityScore
    ): array {
        
        $recommendations = [];

        match ($phase) {
            LifecyclePhase::RISING => $recommendations[] = 'Focus on growth and expansion',
            LifecyclePhase::PEAK => $recommendations[] = 'Maintain stability and prepare for challenges',
            LifecyclePhase::DECLINING => $recommendations[] = 'Implement reforms and crisis management',
            LifecyclePhase::COLLAPSING => $recommendations[] = 'Emergency measures or controlled reset',
            LifecyclePhase::STAGNANT => $recommendations[] = 'Introduce external shocks or innovations',
            default => $recommendations[] = 'Monitor and maintain current state'
        };

        if ($collapseRisk > 0.7) {
            $recommendations[] = 'High collapse risk - immediate intervention required';
        }

        if ($stabilityScore < 0.4) {
            $recommendations[] = 'Low stability - address entropy and population issues';
        }

        return $recommendations;
    }

    public function predictLongTermEvolution(WorldAggregate $world, Collection $characters): array
    {
        $currentTick = $world->currentTick();
        $predictions = [];

        // Simulate next 100 ticks
        for ($i = 1; $i <= 100; $i += 10) {
            $futureTick = $currentTick + $i;
            $futureEntropy = min(1.0, $world->currentEntropy()->value() + ($i * 0.02));
            
            $phase = $this->determineLifecyclePhase(
                $futureTick, 
                new EntropyScore($futureEntropy), 
                $characters->count(), // Simplified
                $characters->count()
            );

            $predictions[] = [
                'tick' => $futureTick,
                'year' => $futureTick * self::TICK_TO_YEARS_RATIO,
                'entropy' => $futureEntropy,
                'phase' => $phase->value,
                'risk_level' => $futureEntropy > 0.8 ? 'critical' : ($futureEntropy > 0.6 ? 'high' : 'moderate')
            ];
        }

        return $predictions;
    }
}

enum LifecyclePhase: string
{
    case RISING = 'rising';
    case PEAK = 'peak';
    case MATURE = 'mature';
    case DECLINING = 'declining';
    case COLLAPSING = 'collapsing';
    case COLLAPSED = 'collapsed';
    case STAGNANT = 'stagnant';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::RISING => '📈 Rise',
            self::PEAK => '🏔️ Peak',
            self::MATURE => '🏛️ Mature',
            self::DECLINING => '📉 Decline',
            self::COLLAPSING => '💥 Collapse',
            self::COLLAPSED => '💀 Collapsed',
            self::STAGNANT => '⏸️ Stagnant',
        };
    }
}

final readonly class WorldLifecycleReport
{
    public function __construct(
        public readonly string $worldId,
        public readonly int $currentTick,
        public readonly int $currentAge,
        public readonly LifecyclePhase $lifecyclePhase,
        public readonly int $remainingLifetime,
        public readonly int $totalPredictedLifetime,
        public readonly float $stabilityScore,
        public readonly float $collapseRisk,
        public readonly float $characterSurvivalRate,
        public readonly HumanCivilizationComparison $humanComparison,
        public readonly array $recommendations,
    ) {}

    public function isLongLived(): bool
    {
        return $this->totalPredictedLifetime > 500; // Longer than average human civilization
    }

    public function isNearCollapse(): bool
    {
        return $this->collapseRisk > 0.8;
    }

    public function getLifecycleEfficiency(): float
    {
        // How efficiently the world uses its lifetime
        $peakTicks = $this->currentTick * 0.4; // Ideal peak at 40% of lifecycle
        $efficiency = 1.0 - abs($this->currentTick - $peakTicks) / $this->totalPredictedLifetime;
        
        return max(0.0, min(1.0, $efficiency));
    }

    public function toArray(): array
    {
        return [
            'world_id' => $this->worldId,
            'current_tick' => $this->currentTick,
            'current_age_years' => $this->currentAge,
            'lifecycle_phase' => $this->lifecyclePhase->value,
            'phase_display' => $this->lifecyclePhase->getDisplayName(),
            'remaining_lifetime_years' => $this->remainingLifetime,
            'total_predicted_lifetime_years' => $this->totalPredictedLifetime,
            'stability_score' => $this->stabilityScore,
            'collapse_risk' => $this->collapseRisk,
            'character_survival_rate' => $this->characterSurvivalRate,
            'is_long_lived' => $this->isLongLived(),
            'is_near_collapse' => $this->isNearCollapse(),
            'lifecycle_efficiency' => $this->getLifecycleEfficiency(),
            'human_comparison' => $this->humanComparison->toArray(),
            'recommendations' => $this->recommendations,
        ];
    }
}

final readonly class HumanCivilizationComparison
{
    public function __construct(
        public readonly int $worldAge,
        public readonly int $humanEquivalentAge,
        public readonly LifecyclePhase $expectedPhase,
        public readonly LifecyclePhase $actualPhase,
        public readonly string $phaseAlignment,
        public readonly string $stabilityComparison,
        public readonly string $lifecycleComparison,
        public readonly bool $isLongerThanAverage,
    ) {}

    public function toArray(): array
    {
        return [
            'world_age_years' => $this->worldAge,
            'human_equivalent_age' => $this->humanEquivalentAge,
            'expected_phase' => $this->expectedPhase->value,
            'actual_phase' => $this->actualPhase->value,
            'phase_alignment' => $this->phaseAlignment,
            'stability_comparison' => $this->stabilityComparison,
            'lifecycle_comparison' => $this->lifecycleComparison,
            'is_longer_than_average' => $this->isLongerThanAverage,
        ];
    }
}
