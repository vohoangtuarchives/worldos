<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\Heroes;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\Heroes\CreateHero\CreateHeroCommand;
use WorldOS\Legacy\Application\Heroes\CreateHero\CreateHeroHandler;

final class CreateHeroController
{
    public function __construct(
        private readonly CreateHeroHandler $handler,
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
        $result = $this->handler->handle(new CreateHeroCommand($name, $worldId));
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'world_id' => $result->worldId,
        ], 201);
    }
}
