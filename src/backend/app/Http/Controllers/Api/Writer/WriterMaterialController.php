<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\Material;
use App\Models\World;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Writer API: Materials for a world (list, activate, adjust, retire).
 */
class WriterMaterialController extends Controller
{
    public function __construct(
        private MaterialRepositoryInterface $repository
    ) {}

    /**
     * GET /api/writer/worlds/{id}/materials/timeline — events (activation, mutation, retirement).
     */
    public function timeline(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }
        $worldId = (string) $id;
        $instances = $this->repository->getInstancesForWorld($worldId);
        $events = [];
        foreach ($instances as $instance) {
            $material = $instance->material ?? null;
            $code = $material ? ($material->code ?? null) : null;
            $mutationState = is_array($instance->mutation_state ?? null) ? $instance->mutation_state : [];
            if ($instance->activation_epoch !== null) {
                $events[] = [
                    'type' => 'activation',
                    'epoch' => $instance->activation_epoch,
                    'material_code' => $code,
                    'description' => "Material " . ($code ?? '') . " activated with strength " . ($instance->strength_level ?? 0),
                    'icon' => 'play-circle',
                    'timestamp' => $instance->created_at instanceof \DateTimeInterface ? $instance->created_at->format('c') : $instance->created_at,
                ];
            }
            if (!empty($mutationState['mutated_from'])) {
                $events[] = [
                    'type' => 'mutation',
                    'epoch' => $mutationState['mutation_epoch'] ?? 0,
                    'material_code' => $code,
                    'description' => 'Material mutated',
                    'from' => $mutationState['mutated_from'],
                    'to' => $code,
                    'pathway' => $mutationState['mutation_pathway'] ?? 'Unknown',
                    'icon' => 'arrow-repeat',
                    'timestamp' => $instance->created_at instanceof \DateTimeInterface ? $instance->created_at->format('c') : $instance->created_at,
                ];
            }
            if ($instance->retired_at ?? null) {
                $events[] = [
                    'type' => 'deactivation',
                    'epoch' => $mutationState['retirement_epoch'] ?? (int) ($world->current_tick ?? 0),
                    'material_code' => $code,
                    'description' => "Material " . ($code ?? '') . " retired",
                    'icon' => 'stop-circle',
                    'timestamp' => $instance->retired_at instanceof \DateTimeInterface ? $instance->retired_at->format('c') : $instance->retired_at,
                ];
            }
        }
        usort($events, fn ($a, $b) => ($a['epoch'] ?? 0) <=> ($b['epoch'] ?? 0));
        return response()->json(['events' => $events]);
    }

    /**
     * GET /api/writer/worlds/{id}/materials
     */
    public function index(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $worldId = (string) $id;
        $instances = $this->repository->getInstancesForWorld($worldId);
        $availableMaterials = $this->repository->getAll();

        return response()->json([
            'instances' => $instances->map(fn ($i) => $this->instanceToArray($i)),
            'available_materials' => $availableMaterials->map(fn ($m) => [
                'id' => $m->id,
                'code' => $m->code ?? null,
                'name' => $m->name ?? $m->code,
            ]),
        ]);
    }

    /**
     * POST /api/writer/worlds/{id}/materials/activate
     * Body: { "material_id": "...", "strength_level": 1-10 }
     */
    public function activate(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'strength_level' => 'required|integer|min:1|max:10',
        ]);

        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }

        $worldId = (string) $id;
        $material = Material::findOrFail($request->material_id);

        $existing = $this->repository->getInstancesForWorld($worldId)
            ->firstWhere('material_id', $material->id);
        if ($existing) {
            $code = is_object($existing->material) ? $existing->material->code : $material->code;
            return response()->json(['error' => "Material {$code} is already active for this world"], 422);
        }

        $instance = $this->repository->createInstance($material, $worldId, [
            'strength_level' => (int) $request->strength_level,
            'activation_epoch' => (int) ($world->current_tick ?? 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Activated {$material->code} with strength {$request->strength_level}.",
            'instance' => $this->instanceToArray($instance),
        ], 201);
    }

    /**
     * PATCH /api/writer/materials/{instanceId}/strength
     * Body: { "strength_level": 0-10 }
     */
    public function adjustStrength(Request $request, string $instanceId): JsonResponse
    {
        $request->validate(['strength_level' => 'required|integer|min:0|max:10']);

        $instance = $this->repository->findInstance($instanceId);
        if (!$instance) {
            return response()->json(['error' => 'Material instance not found'], 404);
        }

        $this->repository->updateInstance($instance, [
            'strength_level' => (int) $request->strength_level,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Adjusted strength to {$request->strength_level}.",
        ]);
    }

    /**
     * POST /api/writer/materials/{instanceId}/retire
     */
    public function retire(string $instanceId): JsonResponse
    {
        $instance = $this->repository->findInstance($instanceId);
        if (!$instance) {
            return response()->json(['error' => 'Material instance not found'], 404);
        }

        if ($instance->retired_at ?? null) {
            return response()->json(['error' => 'Material is already retired'], 422);
        }

        $this->repository->updateInstance($instance, ['retired_at' => now()]);

        $code = is_object($instance->material) ? $instance->material->code : 'material';
        return response()->json([
            'success' => true,
            'message' => "Retired {$code}.",
        ]);
    }

    private function instanceToArray($instance): array
    {
        $material = $instance->material ?? null;
        $retiredAt = $instance->retired_at ?? null;
        return [
            'id' => $instance->id ?? null,
            'material_id' => $instance->material_id ?? null,
            'material_code' => $material ? ($material->code ?? null) : null,
            'world_id' => $instance->world_id ?? null,
            'strength_level' => (int) ($instance->strength_level ?? 0),
            'activation_epoch' => $instance->activation_epoch ?? null,
            'retired_at' => $retiredAt instanceof \DateTimeInterface ? $retiredAt->format('c') : $retiredAt,
            'mutation_state' => $instance->mutation_state ?? [],
        ];
    }
}
