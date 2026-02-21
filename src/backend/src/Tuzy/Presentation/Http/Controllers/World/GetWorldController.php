<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Application\World\GetWorld\GetWorldHandler;
use Tuzy\Application\World\GetWorld\GetWorldQuery;

final class GetWorldController
{
    public function __construct(
        private readonly GetWorldHandler $getWorldHandler,
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $query = new GetWorldQuery($id);
        $world = $this->getWorldHandler->handle($query);
        return response()->json([
            'id' => $world->getId(),
            'name' => $world->getName(),
        ]);
    }
}
