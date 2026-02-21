<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Evolution;

use Tuzy\Domain\Evolution\Contract\EntropyLedgerInterface;

class InMemoryEntropyLedger implements EntropyLedgerInterface
{
    private array $logs = [];

    public function record(
        string $worldId,
        string $sourceType,
        float $deltaEntropy,
        int $tick,
        array $metadata = [],
        ?string $sagaId = null
    ): void {
        $this->logs[] = [
            'world_id' => $worldId,
            'source_type' => $sourceType,
            'delta_entropy' => $deltaEntropy,
            'tick' => $tick,
            'metadata' => $metadata,
            'saga_id' => $sagaId
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}
