<?php

namespace Tuzy\Application\WorldManagement\Services;

use Tuzy\Domain\World\ValueObject\WorldHealthStatus;
use App\Models\World;
use App\Models\WorldAlert;
use Illuminate\Support\Facades\DB;

use Tuzy\Domain\WorldManagement\ValueObject\HealthResult;

class AlertService
{
    protected WorldHealthCalculator $calculator;

    public function __construct(WorldHealthCalculator $calculator = null)
    {
        $this->calculator = $calculator ?? new WorldHealthCalculator();
    }

    public function checkHealth(World $world, array $metrics): WorldHealthStatus
    {
        // 1. Calculate Health (Pure)
        $result = $this->calculator->calculate($world, $metrics);

        // 2. Persist Alerts based on Violations
        foreach ($result->violations as $violation) {
            // Check if active alert exists to avoid spamming?
            // Operator Spec says "Alerts are append-only" but we should avoid dupe every tick.
            // For MVP, lets create if not recently created (e.g. last 1 min) or just create.
            // Operator Spec: "Only emit when Status changes or Threshold triggers"
            // Let's create for now, optimization later.
            
            // Optimization: check existing unresolved alert of same code
            $exists = WorldAlert::where('world_id', $world->id)
                ->where('type', $violation['code'])
                ->where('resolved', false)
                ->exists();

            if (!$exists) {
                WorldAlert::create([
                    'world_id' => $world->id,
                    'type' => $violation['code'],
                    'severity' => ($result->status === WorldHealthStatus::CRITICAL) ? 'CRITICAL' : 'WARNING',
                    'message' => $violation['message'],
                    'details' => ['metrics' => $metrics],
                ]);
            }
        }

        // 3. Update World Status if changed
        // Operator Spec: "Status is single source of truth"
        if ($world->health_status !== $result->status) {
            // Don't auto-recover from HALTED
            if ($world->health_status === WorldHealthStatus::HALTED) {
                return $world->health_status;
            }
            
            $world->health_status = $result->status;
            $world->save();
        }

        return $result->status;
    }
}
