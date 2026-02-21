<?php

declare(strict_types=1);

namespace WorldOS\Infrastructure\Persistence\Evolution;

use WorldOS\Domains\Evolution\Contracts\EntropyLedgerInterface;
use App\Models\SagaEntropyLedger;

class EloquentEntropyLedger implements EntropyLedgerInterface
{
    public function record(
        string $worldId,
        string $sourceType,
        float $deltaEntropy,
        int $tick,
        array $metadata = [],
        ?string $sagaId = null
    ): void {
        SagaEntropyLedger::create([
            'world_id' => $worldId,
            'source_type' => $sourceType,
            'delta_entropy' => $deltaEntropy,
            'tick' => $tick,
            'metadata' => $metadata,
            'saga_id' => $sagaId
        ]);
    }
}
