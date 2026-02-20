<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\World;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tuzy\Application\World\CreateWorld\CreateWorldCommand;
use Tuzy\Application\World\CreateWorld\CreateWorldHandler;

/**
 * Admin API: Worlds CRUD (list, create, show, update).
 * Create flow uses Tuzy CreateWorldHandler (Phase 3).
 */
class AdminWorldController extends Controller
{
    public function index(): JsonResponse
    {
        $worlds = World::select(['id', 'name', 'health_status', 'status', 'current_tick', 'preset', 'genre', 'created_at', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($worlds->map(function (World $w) {
            return [
                'id' => $w->id,
                'name' => $w->name,
                'health_status' => $w->health_status instanceof \BackedEnum ? $w->health_status->value : $w->health_status,
                'status' => $w->status,
                'current_tick' => (int) ($w->current_tick ?? 0),
                'preset' => $w->preset,
                'genre' => $w->genre,
                'created_at' => $w->created_at?->toIso8601String(),
                'updated_at' => $w->updated_at?->toIso8601String(),
            ];
        }));
    }

    public function show(string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }
        return response()->json([
            'id' => $world->id,
            'name' => $world->name,
            'health_status' => $world->health_status instanceof \BackedEnum ? $world->health_status->value : $world->health_status,
            'status' => $world->status,
            'current_tick' => (int) ($world->current_tick ?? 0),
            'description' => $world->description ?? null,
            'tags' => $world->tags ?? [],
            'law_profile' => is_object($world->law_profile) ? $world->law_profile->toArray() : $world->law_profile,
            'created_at' => $world->created_at?->toIso8601String(),
            'updated_at' => $world->updated_at?->toIso8601String(),
        ]);
    }

    public function store(Request $request, CreateWorldHandler $createWorldHandler): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:64',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $result = $createWorldHandler->handle(new CreateWorldCommand($validated['name']));

        $world = World::find($result->id);
        if ($world) {
            $world->update([
                'type' => $validated['type'] ?? \App\Domains\World\Enums\WorldType::FANTASY,
                'description' => $validated['description'] ?? null,
                'tags' => $validated['tags'] ?? [],
                'status' => 'ACTIVE',
                'health_status' => \App\Domains\World\Enums\WorldHealthStatus::STABLE,
            ]);
            try {
                if (method_exists($world, 'clock') && class_exists(\App\Models\WorldClock::class)) {
                    $world->clock()->create(['current_tick' => 0]);
                }
            } catch (\Throwable $e) {
                // clock table may not exist
            }
            try {
                app(\App\Domains\Runtime\UniverseFactory::class)->spawnFromWorld($world);
            } catch (\Throwable $e) {
                // Non-fatal: World created; Universe can be created later
            }
        }

        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'message' => "World '{$result->name}' created successfully.",
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $world = World::find($id);
        if (!$world) {
            return response()->json(['error' => 'World not found'], 404);
        }
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);
        $world->update(array_filter($validated));
        return response()->json(['success' => true, 'message' => 'World updated.', 'world' => ['id' => $world->id, 'name' => $world->name]]);
    }
}
