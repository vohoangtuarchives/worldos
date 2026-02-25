<?php

declare(strict_types=1);

namespace App\Modules\Universe\ValueObjects;

use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\StabilityMetric;
use App\Modules\Shared\ValueObjects\WorldStateVector;
use DateTimeImmutable;

/**
 * Universe Snapshot — immutable record of universe state at a specific tick.
 *
 * Enables time travel, forking, and AI analysis without modifying history.
 * "Snapshot-first" approach: each tick generates a new snapshot.
 */
final readonly class UniverseSnapshot
{
    /**
     * @param array<string, mixed>|null $metrics Additional metrics for AI analysis
     */
    public function __construct(
        public UniverseId $universeId,
        public int $tick,
        public WorldStateVector $stateVector,
        public ?CascadeStateVector $cascadeState,
        public ?StabilityMetric $stability,
        public ?array $metrics,
        public DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'universe_id' => $this->universeId->value,
            'tick' => $this->tick,
            'state_vector' => $this->stateVector->toArray(),
            'cascade_state' => $this->cascadeState?->toArray(),
            'stability_metric' => $this->stability?->value,
            'entropy' => $this->stateVector->entropy,
            'metrics' => $this->metrics,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
