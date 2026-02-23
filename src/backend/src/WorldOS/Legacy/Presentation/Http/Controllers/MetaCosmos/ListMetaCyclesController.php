<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\MetaCosmos;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMetaCyclesController
{
    public function __invoke(string $id, Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [],
        ]);
    }
}
