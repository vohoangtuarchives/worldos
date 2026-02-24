<?php

namespace App\Exceptions\Simulation;

use App\Exceptions\WorldOSException;

class SimulationException extends WorldOSException
{
    public const INVALID_PHASE = 'SIM_INVALID_PHASE';
    public const STATE_CORRUPTION = 'SIM_STATE_CORRUPTION';
    public const PIPELINE_FAILURE = 'SIM_PIPELINE_FAILURE';
    public const RESOURCE_EXHAUSTION = 'SIM_RESOURCE_EXHAUSTION';
    public const TIMELINE_FORK_FAILED = 'SIM_TIMELINE_FORK_FAILED';
    public const REPLAY_ERROR = 'SIM_REPLAY_ERROR';

    public static function invalidPhase(string $phase, array $context = []): self
    {
        return new self(
            "Invalid simulation phase: {$phase}",
            self::INVALID_PHASE,
            array_merge(['phase' => $phase], $context)
        );
    }

    public static function stateCorruption(string $details, array $context = []): self
    {
        return new self(
            "Simulation state corruption detected: {$details}",
            self::STATE_CORRUPTION,
            array_merge(['details' => $details], $context)
        );
    }

    public static function pipelineFailure(string $pipeline, string $reason, array $context = []): self
    {
        return new self(
            "Simulation pipeline '{$pipeline}' failed: {$reason}",
            self::PIPELINE_FAILURE,
            array_merge(['pipeline' => $pipeline, 'reason' => $reason], $context)
        );
    }

    public static function resourceExhaustion(string $resource, array $context = []): self
    {
        return new self(
            "Simulation resource exhausted: {$resource}",
            self::RESOURCE_EXHAUSTION,
            array_merge(['resource' => $resource], $context)
        );
    }

    public static function timelineForkFailed(string $timelineId, string $reason, array $context = []): self
    {
        return new self(
            "Failed to fork timeline {$timelineId}: {$reason}",
            self::TIMELINE_FORK_FAILED,
            array_merge(['timeline_id' => $timelineId, 'reason' => $reason], $context)
        );
    }

    public static function replayError(string $timelineId, string $reason, array $context = []): self
    {
        return new self(
            "Timeline replay error for {$timelineId}: {$reason}",
            self::REPLAY_ERROR,
            array_merge(['timeline_id' => $timelineId, 'reason' => $reason], $context)
        );
    }
}
