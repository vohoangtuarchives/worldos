<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Application\World\CreateWorld\CreateWorldCommand;
use Tuzy\Application\World\CreateWorld\CreateWorldHandler;

final class CreateWorldController
{
    public function __construct(
        private readonly CreateWorldHandler $createWorldHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        if ($name === '') {
            return response()->json(['message' => 'name is required'], 422);
        }
        $command = new CreateWorldCommand($name);
        $result = $this->createWorldHandler->handle($command);
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
        ], 201);
    }
}
