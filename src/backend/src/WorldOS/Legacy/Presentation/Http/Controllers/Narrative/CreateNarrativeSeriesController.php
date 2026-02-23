<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\Narrative;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\Narrative\CreateNarrativeSeries\CreateNarrativeSeriesCommand;
use WorldOS\Legacy\Application\Narrative\CreateNarrativeSeries\CreateNarrativeSeriesHandler;

final class CreateNarrativeSeriesController
{
    public function __construct(
        private readonly CreateNarrativeSeriesHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $title = (string) $request->input('title', '');
        if ($title === '') {
            return response()->json(['message' => 'title is required'], 422);
        }
        $result = $this->handler->handle(new CreateNarrativeSeriesCommand($title));
        return response()->json(['id' => $result->id, 'title' => $result->title], 201);
    }
}
