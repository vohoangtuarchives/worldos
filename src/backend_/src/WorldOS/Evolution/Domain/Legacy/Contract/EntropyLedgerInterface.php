<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Contract;

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
