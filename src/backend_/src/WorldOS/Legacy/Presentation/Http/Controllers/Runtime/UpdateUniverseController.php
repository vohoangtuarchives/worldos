<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\Runtime;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\Runtime\UpdateUniverse\UpdateUniverseCommand;
use WorldOS\Legacy\Application\Runtime\UpdateUniverse\UpdateUniverseHandler;

final class UpdateUniverseController
{
    public function __construct(
        private readonly UpdateUniverseHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $command = new UpdateUniverseCommand(
            $id,
            $request->input('name'),
            (int)$request->input('age'),
            $request->input('status'),
            (float)$request->input('entropy'),
            (float)$request->input('stability_index')
        );

        $this->handler->handle($command);

        return response()->json(['success' => true]);
    }
}
