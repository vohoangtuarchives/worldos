<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\World\CreateWorld\CreateWorldCommand;
use WorldOS\Legacy\Application\World\CreateWorld\CreateWorldHandler;

final class CreateWorldController
{
    public function __construct(
        private readonly CreateWorldHandler $createWorldHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        $preset = (string) $request->input('preset', 'default');
        $originType = (string) $request->input('origin_type', 'user_created');
        
        if ($name === '') {
            return response()->json(['message' => 'name is required'], 422);
        }
        $command = new CreateWorldCommand($name, $preset, $originType);
        $result = $this->createWorldHandler->handle($command);
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
        ], 201);
    }
}
