<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\Saga;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Application\Saga\CreateSaga\CreateSagaCommand;
use Tuzy\Application\Saga\CreateSaga\CreateSagaHandler;

final class CreateSagaController
{
    public function __construct(
        private readonly CreateSagaHandler $createSagaHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        if ($name === '') {
            return response()->json(['message' => 'name is required'], 422);
        }
        $command = new CreateSagaCommand($name);
        $result = $this->createSagaHandler->handle($command);
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
        ], 201);
    }
}
