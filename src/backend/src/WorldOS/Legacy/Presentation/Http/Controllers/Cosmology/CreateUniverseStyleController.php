<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\Cosmology;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleCommand;
use WorldOS\Legacy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleHandler;

final class CreateUniverseStyleController
{
    public function __construct(
        private readonly CreateUniverseStyleHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        $worldId = (string) $request->input('world_id', '');
        if ($name === '' || $worldId === '') {
            return response()->json(
                ['message' => 'name and world_id are required'],
                422
            );
        }
        $command = new CreateUniverseStyleCommand($name, $worldId);
        $result = $this->handler->handle($command);
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'world_id' => $result->worldId,
        ], 201);
    }
}
