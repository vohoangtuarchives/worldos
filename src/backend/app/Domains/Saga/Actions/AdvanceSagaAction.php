<?php

namespace App\Domains\Saga\Actions;

use App\Domains\Saga\Saga;
use App\Domains\Saga\Services\SagaService;

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
