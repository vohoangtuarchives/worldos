<?php

namespace WorldOS\Legacy\Application\WorldManagement\Services;

use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus;
use WorldOS\Blueprint\Domain\LegacyManagement\ValueObject\HealthResult;
use App\Models\World;
use Illuminate\Support\Facades\DB;

class WorldHealthCalculator
{
    /**
     * PURE FUNCTION: Calculates health based on input metrics only.
     * No side effects.
     */
    public function calculate(World $world, array $metrics): HealthResult
    {
        // 1. Gather Indicators
        $rejectRate = $this->calculateRejectRate($world->id);
        $forkRate = $metrics['fork_rate'] ?? 0; // Forks in last 24h
        $economyStress = $this->calculateEconomyStress($metrics);
        $fatalAttempts = $metrics['fatal_attempts'] ?? 0;

        $violations = [];
        $status = WorldHealthStatus::STABLE;

        // 2. Evaluate CRITICAL Rules (Priority 1)
        if ($rejectRate >= 40) {
            $status = WorldHealthStatus::CRITICAL;
            $violations[] = ['code' => 'LAW_REJECT_RATE', 'message' => "Reject Rate {$rejectRate}% >= 40%"];
        }
        
        if ($fatalAttempts >= 1) {
             $status = WorldHealthStatus::CRITICAL;
             $violations[] = ['code' => 'FATAL_LAW_ATTEMPT', 'message' => "Fatal Law Attempt Detected"];
        }

        if ($economyStress >= 90) { // Economy Dead
             $status = WorldHealthStatus::CRITICAL;
             $violations[] = ['code' => 'ECONOMY_DEAD', 'message' => "Economy Collapse (Stress {$economyStress})"];
        }

        // If already critical, we can return or keep collecting violations
        // Operator Spec: Alert System "Alerts are append-only" and "Scoreboard" style.
        // We should probably report ALL violations.
        
        // 3. Evaluate DEGRADED Rules (Priority 2)
        // If critical, we still want to know if these thresholds are met?
        // Actually, if we are CRITICAL due to Reject Rate, we might also want to know about Fork Explosion.
        // Let's collect ALL violations.
        // 3. Evaluate DEGRADED Rules (Priority 2)
        // Only set to DEGRADED if we are currently STABLE.
        // If we are CRITICAL, we stay CRITICAL.
        
        if ($rejectRate >= 15) {
            if ($status === WorldHealthStatus::STABLE) $status = WorldHealthStatus::DEGRADED;
            $violations[] = ['code' => 'LAW_REJECT_SPIKE', 'message' => "Reject Rate {$rejectRate}% >= 15%"];
        }

        if ($forkRate >= 3) {
            if ($status === WorldHealthStatus::STABLE) $status = WorldHealthStatus::DEGRADED;
            $violations[] = ['code' => 'FORK_EXPLOSION', 'message' => "Fork Rate {$forkRate}/day >= 3"];
        }

        if ($economyStress >= 70) {
            if ($status === WorldHealthStatus::STABLE) $status = WorldHealthStatus::DEGRADED;
             $violations[] = ['code' => 'ECONOMY_INSTABILITY', 'message' => "Economy Instability (Stress {$economyStress})"];
        }

        return new HealthResult($status, $violations);
    }

    protected function calculateRejectRate(string $worldId): float
    {
        // Look at last 100 generations (Operator Spec: rolling 100 events)
        $last100 = DB::table('ai_generations')
            ->where('world_id', $worldId)
            ->latest()
            ->limit(100)
            ->get();

        if ($last100->isEmpty()) return 0.0;

        $rejected = $last100->where('status', 'REJECTED')->count();
        return ($rejected / $last100->count()) * 100;
    }

    protected function calculateEconomyStress(array $metrics): float
    {
        // Simple average of faction stress
        $stats = $metrics['faction_stats'] ?? [];
        if (empty($stats)) return 0.0;

        $totalStress = array_sum(array_column($stats, 'stress'));
        return $totalStress / count($stats);
    }
}
