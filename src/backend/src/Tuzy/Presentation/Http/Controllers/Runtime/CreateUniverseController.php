<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\Runtime;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Application\Runtime\CreateUniverse\CreateUniverseCommand;
use Tuzy\Application\Runtime\CreateUniverse\CreateUniverseHandler;

final class CreateUniverseController
{
    public function __construct(
        private readonly CreateUniverseHandler $createUniverseHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        if ($name === '') {
            return response()->json(['message' => 'name is required'], 422);
        }
        $command = new CreateUniverseCommand($name);
        $result = $this->createUniverseHandler->handle($command);
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
        ], 201);
    }
}
