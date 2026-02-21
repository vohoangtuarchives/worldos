<?php

declare(strict_types=1);

namespace Tuzy\Presentation\Http\Controllers\MetaCosmos;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RunMetaCycleController
{
    public function __invoke(string $id, Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Not implemented yet.',
        ]);
    }
}
