<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\Genesis;

use Illuminate\Http\JsonResponse;
use WorldOS\Legacy\Application\Saga\Services\GenesisPresetService;

final class ListPresetsController
{
    public function __construct(
        private readonly GenesisPresetService $presetService
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $categories = $this->presetService->allByCategory();
        
        return response()->json([
            'data' => $categories
        ]);
    }
}
