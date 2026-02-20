<?php

namespace App\Http\Controllers\Api\Writer;

use App\Http\Controllers\Controller;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Models\World;
use App\Models\UniverseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tuzy\Domain\World\Exception\WorldNotFoundException;

/**
 * Writer API: Worlds (aggregate root). World Detail includes Runtime Instances (Universes).
 */
class WriterWorldController extends Controller
{
    /**
     * List worlds for Writer flow. World = aggregate root; Universe = runtime instance.
     */
    public function index()
    {
        $worlds = World::select([
            'id', 'name', 'health_status', 'status', 'current_tick', 'preset', 'genre',
            'created_at', 'updated_at'
        ])->orderBy('updated_at', 'desc')->get();

        return response()->json($worlds->map(function (World $w) {
            return [
                'id' => (string) $w->id,
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

    /**
     * World detail with runtime instances (Universes belonging to this World).
     */
    public function show(string $id)
    {
        $world = World::find($id);
        if (! $world) {
            throw WorldNotFoundException::withId($id);
        }
        $instances = UniverseModel::where('world_id', $world->id)->get()->map(function (UniverseModel $u) {
            return [
                'id' => (string) $u->id,
                'name' => $u->name,
                'age' => (int) ($u->age ?? 0),
                'state_vector' => $u->state_vector,
                'entropy' => $u->entropy !== null ? (float) $u->entropy : null,
                'stability_index' => $u->stability_index !== null ? (float) $u->stability_index : null,
                'status' => $u->status ?? 'running',
                'is_archived' => $u->is_archived ?? false,
                'created_at' => $u->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'id' => (string) $world->id,
            'name' => $world->name,
            'health_status' => $world->health_status instanceof \BackedEnum ? $world->health_status->value : $world->health_status,
            'status' => $world->status,
            'current_tick' => (int) ($world->current_tick ?? 0),
            'preset' => $world->preset,
            'genre' => $world->genre,
            'law_profile' => is_object($world->law_profile) ? $world->law_profile->toArray() : $world->law_profile,
            'created_at' => $world->created_at?->toIso8601String(),
            'updated_at' => $world->updated_at?->toIso8601String(),
            'runtime_instances' => $instances,
        ]);
    }

    /**
     * Create a new Runtime Instance (Universe) under this World.
     * POST /api/writer/worlds/{id}/instances
     */
    public function storeInstance(Request $request, string $id)
    {
        $world = World::find($id);
        if (! $world) {
            throw WorldNotFoundException::withId($id);
        }
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'archetype' => 'nullable|string|in:BALANCED,UTOPIAN,DYSTOPIAN,CHAOTIC,VOID_TOUCHED',
        ]);

        $uuid = (string) Str::uuid();
        $repo = app(CosmologyRepository::class);
        $universe = $repo->createCustom($uuid, [
            'name' => $data['name'] ?? ($world->name . ' Instance'),
            'archetype' => $data['archetype'] ?? 'BALANCED',
            'world_id' => $world->id,
        ]);

        $model = UniverseModel::find($universe->getId());
        $state = $universe->getState();

        return response()->json([
            'id' => $universe->getId(),
            'name' => $model?->name ?? ('Universe ' . substr($universe->getId(), 0, 8)),
            'age' => $universe->getAge(),
            'world_id' => $world->id,
            'state_vector' => [
                'order' => $state->getOrder(),
                'entropy' => $state->getEntropy(),
            ],
            'created_at' => $model?->created_at?->toIso8601String(),
        ], 201);
    }
}
