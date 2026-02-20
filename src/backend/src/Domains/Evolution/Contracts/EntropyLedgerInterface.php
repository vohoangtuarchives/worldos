<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Contracts;

interface EntropyLedgerInterface
{
    public function record(
        string $worldId,
        string $sourceType,
        float $deltaEntropy,
        int $tick,
        array $metadata = [],
        ?string $sagaId = null
    ): void;
}
