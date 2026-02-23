<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use WorldOS\Legacy\Application\World\DeleteWorld\DeleteWorldCommand;
use WorldOS\Legacy\Application\World\DeleteWorld\DeleteWorldHandler;

final class DeleteWorldController
{
    public function __construct(
        private readonly DeleteWorldHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteWorldCommand($id);
        $this->handler->handle($command);

        return response()->json(['success' => true]);
    }
}
