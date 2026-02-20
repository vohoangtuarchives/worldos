<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\Evolution;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileCommand;
use Tuzy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileHandler;

final class CreateEvolutionProfileController
{
    public function __construct(
        private readonly CreateEvolutionProfileHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        if ($name === '') {
            return response()->json(['message' => 'name is required'], 422);
        }
        $result = $this->handler->handle(new CreateEvolutionProfileCommand($name));
        return response()->json(['id' => $result->id, 'name' => $result->name], 201);
    }
}
