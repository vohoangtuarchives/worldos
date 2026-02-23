<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\World\ListWorlds\ListWorldsHandler;
use WorldOS\Legacy\Application\World\ListWorlds\ListWorldsQuery;

final class ListWorldsController
{
    public function __construct(
        private readonly ListWorldsHandler $listWorldsHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = new ListWorldsQuery();
        $result = $this->listWorldsHandler->handle($query);
        
        return response()->json($result->worlds);
    }
}
