<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\World\UpdateWorld\UpdateWorldCommand;
use WorldOS\Legacy\Application\World\UpdateWorld\UpdateWorldHandler;

final class UpdateWorldController
{
    public function __construct(
        private readonly UpdateWorldHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $command = new UpdateWorldCommand(
            $id,
            $request->input('name'),
            $request->input('status'),
            $request->input('health_status'),
            (int)$request->input('current_tick'),
            $request->input('origin_type'),
            $request->input('preset')
        );

        $this->handler->handle($command);

        return response()->json(['success' => true]);
    }
}
