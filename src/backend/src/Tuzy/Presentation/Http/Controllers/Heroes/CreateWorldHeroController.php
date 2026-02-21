<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\Heroes;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Application\Heroes\CreateWorldHero\CreateWorldHeroCommand;
use Tuzy\Application\Heroes\CreateWorldHero\CreateWorldHeroHandler;

final class CreateWorldHeroController
{
    public function __construct(
        private readonly CreateWorldHeroHandler $handler,
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
        $result = $this->handler->handle(new CreateWorldHeroCommand($name, $worldId));
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'world_id' => $result->worldId,
        ], 201);
    }
}
