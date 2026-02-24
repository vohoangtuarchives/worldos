<?php

namespace WorldOS\Legacy\Application\Saga\Actions;

use WorldOS\Saga\Domain\Legacy\Saga;
use WorldOS\Legacy\Application\Saga\Services\SagaService;

class AdvanceSagaAction
{
    public function __construct(
        private SagaService $sagaService
    ) {}

    public function execute(Saga $saga, int $ticks): void
    {
        if ($saga->isComplete()) {
            throw new \RuntimeException('Cannot advance a completed Saga.');
        }

        $this->sagaService->runBatch($saga, $ticks);
    }
}
