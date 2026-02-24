<?php

namespace WorldOS\Legacy\Application\Saga\Actions;

use WorldOS\Saga\Domain\Legacy\Saga;
use Illuminate\Support\Str;

class CreateSagaAction
{
    public function execute(string $name, int $worldCount, array $metadata = []): Saga
    {
        return Saga::create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'world_count' => $worldCount,
            'status' => Saga::STATUS_PENDING,
            'metadata' => $metadata,
        ]);
    }
}
