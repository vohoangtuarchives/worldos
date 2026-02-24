<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\Runtime;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\Runtime\GetUniverse\GetUniverseHandler;
use WorldOS\Legacy\Application\Runtime\GetUniverse\GetUniverseQuery;

final class GetUniverseController
{
    public function __construct(
        private readonly GetUniverseHandler $getUniverseHandler,
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $query = new GetUniverseQuery($id);
        $u = $this->getUniverseHandler->handle($query);
        
        return response()->json([
            'id' => $u->getId(),
            'name' => $u->getName(),
            'age' => $u->getAge(),
            'status' => $u->getStatus(),
            'state_vector' => $u->getStateVector(),
            'entropy' => $u->getEntropy(),
            'stability_index' => $u->getStabilityIndex(),
        ]);
    }
}
