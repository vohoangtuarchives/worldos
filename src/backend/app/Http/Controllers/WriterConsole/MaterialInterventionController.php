<?php

namespace App\Http\Controllers\WriterConsole;

use App\Http\Controllers\Controller;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\Material;
use App\Models\World;
use Illuminate\Http\Request;
use Tuzy\Domain\World\Exception\WorldNotFoundException;

class MaterialInterventionController extends Controller
{
    private MaterialRepositoryInterface $repository;

    public function __construct(MaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show material state viewer for a world.
     */
    public function index(Request $request, string $worldId)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $instances = $this->repository->getInstancesForWorld($worldId);
        $availableMaterials = Material::all();

        return view('writer.materials.state-viewer', [
            'world' => $world,
            'instances' => $instances,
            'availableMaterials' => $availableMaterials,
        ]);
    }

    /**
     * Manually activate a dormant material.
     */
    public function activate(Request $request, string $worldId)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'strength_level' => 'required|integer|min:1|max:10',
        ]);

        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $material = Material::findOrFail($request->material_id);

        // Check if material is already active
        $existing = $this->repository->getInstancesForWorld($worldId)
            ->where('material_id', $material->id)
            ->where('activation_epoch', '!=', null)
            ->first();

        if ($existing) {
            return back()->with('error', 'Material is already active.');
        }

        // Create or activate instance
        $instance = $this->repository->createInstance($material, $worldId, [
            'strength_level' => $request->strength_level,
            'activation_epoch' => $world->tick,
        ]);

        return back()->with('success', "Activated {$material->code} with strength {$request->strength_level}.");
    }

    /**
     * Adjust material strength.
     */
    public function adjustStrength(Request $request, string $instanceId)
    {
        $request->validate([
            'strength_level' => 'required|integer|min:0|max:10',
        ]);

        $instance = $this->repository->findInstance($instanceId);

        if (!$instance) {
            return back()->with('error', 'Material instance not found.');
        }

        $this->repository->updateInstance($instance, [
            'strength_level' => $request->strength_level,
        ]);

        return back()->with('success', "Adjusted strength to {$request->strength_level}.");
    }

    /**
     * Retire a material early.
     */
    public function retire(Request $request, string $instanceId)
    {
        $instance = $this->repository->findInstance($instanceId);

        if (!$instance) {
            return back()->with('error', 'Material instance not found.');
        }

        if ($instance->retired_at) {
            return back()->with('error', 'Material is already retired.');
        }

        $this->repository->updateInstance($instance, [
            'retired_at' => now(),
        ]);

        return back()->with('success', "Retired {$instance->material->code}.");
    }

    /**
     * Force a material mutation.
     */
    public function forceMutation(Request $request, string $instanceId)
    {
        $request->validate([
            'target_code' => 'required|string',
        ]);

        $instance = $this->repository->findInstance($instanceId);

        if (!$instance) {
            return back()->with('error', 'Material instance not found.');
        }

        $targetMaterial = $this->repository->findByCode($request->target_code);

        if (!$targetMaterial) {
            return back()->with('error', 'Target material not found.');
        }

        // Create mutated instance
        $mutatedInstance = $this->repository->createInstance($targetMaterial, $instance->world_id, [
            'strength_level' => (int)($instance->strength_level * 0.7),
            'activation_epoch' => $instance->world->tick ?? 0,
            'mutation_state' => [
                'mutated_from' => $instance->material->code,
                'mutation_pathway' => 'Manual intervention by writer',
                'mutation_epoch' => $instance->world->tick ?? 0,
            ],
        ]);

        // Mark original as mutated
        $this->repository->updateInstance($instance, [
            'mutation_state' => [
                'mutated_to' => $targetMaterial->code,
                'mutation_epoch' => $instance->world->tick ?? 0,
                'pathway_description' => 'Manual intervention by writer',
            ],
            'strength_level' => (int)($instance->strength_level * 0.3),
        ]);

        return back()->with('success', "Forced mutation from {$instance->material->code} to {$targetMaterial->code}.");
    }

    /**
     * Show material event timeline for a world.
     */
    public function timeline(string $worldId)
    {
        $world = World::find($worldId);
        if (!$world) {
            throw WorldNotFoundException::withId($worldId);
        }
        $materials = Material::all();
        
        // Build events from material instances
        $instances = $this->repository->getInstancesForWorld($worldId);
        $events = [];

        foreach ($instances as $instance) {
            // Activation event
            if ($instance->activation_epoch !== null) {
                $events[] = [
                    'type' => 'activation',
                    'epoch' => $instance->activation_epoch,
                    'material_code' => $instance->material->code,
                    'description' => "Material {$instance->material->code} activated with strength {$instance->strength_level}",
                    'icon' => 'play-circle',
                    'timestamp' => $instance->created_at,
                ];
            }

            // Mutation events
            if (isset($instance->mutation_state['mutated_from'])) {
                $events[] = [
                    'type' => 'mutation',
                    'epoch' => $instance->mutation_state['mutation_epoch'] ?? 0,
                    'material_code' => $instance->material->code,
                    'description' => "Material mutated",
                    'from' => $instance->mutation_state['mutated_from'],
                    'to' => $instance->material->code,
                    'pathway' => $instance->mutation_state['mutation_pathway'] ?? 'Unknown',
                    'icon' => 'arrow-repeat',
                    'timestamp' => $instance->created_at,
                ];
            }

            // Retirement event
            if ($instance->retired_at) {
                $retirementEpoch = $instance->mutation_state['retirement_epoch'] ?? $world->tick;
                $events[] = [
                    'type' => 'deactivation',
                    'epoch' => $retirementEpoch,
                    'material_code' => $instance->material->code,
                    'description' => "Material {$instance->material->code} retired",
                    'icon' => 'stop-circle',
                    'timestamp' => $instance->retired_at,
                ];
            }
        }

        // Sort by epoch
        usort($events, fn($a, $b) => $a['epoch'] <=> $b['epoch']);

        return view('writer.materials.timeline', [
            'world' => $world,
            'materials' => $materials,
            'events' => $events,
        ]);
    }
}
