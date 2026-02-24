<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Presentation\Http\Controllers\World;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WorldOS\Legacy\Application\World\GetWorld\GetWorldHandler;
use WorldOS\Legacy\Application\World\GetWorld\GetWorldQuery;

final class GetWorldController
{
    public function __construct(
        private readonly GetWorldHandler $getWorldHandler,
        private readonly \WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface $universeRepository
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $query = new GetWorldQuery($id);
        $world = $this->getWorldHandler->handle($query);

        $universes = $this->universeRepository->findByWorldId($world->getId());
        $runtime_instances = [];
        foreach ($universes as $u) {
            $runtime_instances[] = [
                'id' => $u->getId(),
                'name' => $u->getName(),
                'age' => $u->getAge(),
                'status' => $u->getStatus(),
            ];
        }

        return response()->json([
            'id' => $world->getId(),
            'name' => $world->getName(),
            'status' => $world->getStatus(),
            'health_status' => $world->getHealthStatus(),
            'current_tick' => $world->getCurrentTick(),
            'origin_type' => $world->getOriginType(),
            'preset' => $world->getPreset(),
            'config' => $world->getConfig(),
            'gene_vector' => $world->getGeneVector(),
            'runtime_instances' => $runtime_instances,
        ]);
    }
}
