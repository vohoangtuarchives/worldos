<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\Runtime;

use Illuminate\Http\JsonResponse;
use WorldOS\Legacy\Application\Runtime\DeleteUniverse\DeleteUniverseCommand;
use WorldOS\Legacy\Application\Runtime\DeleteUniverse\DeleteUniverseHandler;

final class DeleteUniverseController
{
    public function __construct(
        private readonly DeleteUniverseHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteUniverseCommand($id);
        $this->handler->handle($command);

        return response()->json(['success' => true]);
    }
}
