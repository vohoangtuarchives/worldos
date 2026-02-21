<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Repositories;

use App\Domains\Cosmology\Contracts\CosmicSnapshotRepositoryInterface;
use Tuzy\Domain\Cosmology\ValueObject\WorldSnapshot;
use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\EnvironmentState;
use Tuzy\Domain\Cosmology\ValueObject\CivilizationState;
use App\Models\CosmicSnapshot;
use App\Models\CosmicEvent;

class CosmicSnapshotEloquentRepository implements CosmicSnapshotRepositoryInterface
{
    public function saveSnapshot(string $worldId, WorldSnapshot $snapshot): void
    {
        CosmicSnapshot::updateOrCreate(
            ['world_id' => $worldId, 'year' => $snapshot->year],
            [
                // Cosmic
                'energy' => $snapshot->cosmic->energy,
                'entropy' => $snapshot->cosmic->entropy,
                'tension' => $snapshot->cosmic->strain,
                'stability' => $snapshot->cosmic->stability,
                'resonance' => $snapshot->cosmic->causality,
                'information_density' => 0.0, // Phase 5: will be derived
                'transcendence' => 0.0,       // Phase 5: will be derived
                'attractor' => $snapshot->cosmic->currentAttractor,
                'attractor_incarnation_id' => $snapshot->cosmic->currentIncarnationId ?? null,
                'morph_target_centroid' => $snapshot->cosmic->morphTargetCentroid ? json_encode($snapshot->cosmic->morphTargetCentroid) : null,
                'morph_start_tick' => $snapshot->cosmic->morphStartTick,
                'morph_intensity' => $snapshot->cosmic->morphIntensity,

                // Environment
                'env_ley_energy' => $snapshot->environment->leyEnergy,
                'env_terrain_stability' => $snapshot->environment->terrainStability,
                'env_biosphere_vitality' => $snapshot->environment->biosphereVitality,
                'env_anomaly_density' => $snapshot->environment->anomalyDensity,

                // Civilization
                'civ_knowledge' => $snapshot->civilization->culturalEnergy,
                'civ_ritual_coherence' => $snapshot->civilization->spiritualCohesion,
                'civ_tech_level' => $snapshot->civilization->technologicalLevel,
                'civ_faction_stability' => $snapshot->civilization->stability,
                'civ_resonance_accumulator' => $snapshot->civilization->resonanceAccumulator,
                'civ_resilience' => $snapshot->civilization->resilience,
                'social_classes' => array_map(fn($c) => $c->toArray(), $snapshot->civilization->socialClasses),

                // Composite
                'composite_tension' => $snapshot->compositeTension(),
            ]
        );
    }

    public function saveEvent(string $worldId, array $event): void
    {
        CosmicEvent::create([
            'world_id' => $worldId,
            'year' => $event['year'] ?? 0,
            'type' => $event['type'] ?? 'UNKNOWN',
            'from_attractor' => $event['from'] ?? '',
            'to_attractor' => $event['to'] ?? '',
            'force' => $event['force'] ?? 0.0,
            'metadata' => $event['new_attractor'] ?? null,
        ]);
    }

    public function latestSnapshot(string $worldId): ?WorldSnapshot
    {
        $model = CosmicSnapshot::where('world_id', $worldId)
            ->orderByDesc('year')
            ->first();

        return $model ? $this->modelToSnapshot($model) : null;
    }

    public function snapshotAt(string $worldId, int $year): ?WorldSnapshot
    {
        $model = CosmicSnapshot::where('world_id', $worldId)
            ->where('year', $year)
            ->first();

        return $model ? $this->modelToSnapshot($model) : null;
    }

    public function timeline(string $worldId, int $limit = 100): array
    {
        return CosmicSnapshot::where('world_id', $worldId)
            ->orderBy('year')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->modelToSnapshot($m))
            ->toArray();
    }

    public function events(string $worldId): array
    {
        return CosmicEvent::where('world_id', $worldId)
            ->orderBy('year')
            ->get()
            ->map(fn ($m) => [
                'year' => $m->year,
                'type' => $m->type,
                'from' => $m->from_attractor,
                'to' => $m->to_attractor,
                'force' => $m->force,
                'metadata' => $m->metadata,
            ])
            ->toArray();
    }

    private function modelToSnapshot(CosmicSnapshot $model): WorldSnapshot
    {
        return new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: $model->entropy,
                energy: $model->energy,
                causality: $model->resonance,
                strain: $model->tension,
                stability: $model->stability,
                currentAttractor: $model->attractor,
                year: $model->year,
                currentIncarnationId: $model->attractor_incarnation_id,
                morphTargetCentroid: $model->morph_target_centroid ? json_decode($model->morph_target_centroid, true) : null,
                morphStartTick: $model->morph_start_tick,
                morphIntensity: $model->morph_intensity ?? 1.0
            ),
            environment: new EnvironmentState(
                leyEnergy: $model->env_ley_energy,
                terrainStability: $model->env_terrain_stability,
                biosphereVitality: $model->env_biosphere_vitality,
                anomalyDensity: $model->env_anomaly_density,
                year: $model->year,
            ),
            civilization: new CivilizationState(
                culturalEnergy: $model->civ_knowledge,
                spiritualCohesion: $model->civ_ritual_coherence,
                technologicalLevel: $model->civ_tech_level,
                stability: $model->civ_faction_stability,
                prosperity: 0.5, // Default or derived
                militaryPressure: 0.1, // Default or derived
                externalThreat: 0.0, // Default or derived
                internalEntropy: 0.1, // Default or derived
                resonanceAccumulator: $model->civ_resonance_accumulator,
                resilience: $model->civ_resilience ?? 1.0,
                year: $model->year,
                socialClasses: $this->hydrateSocialClasses($model->social_classes),
            ),
            year: $model->year,
        );
    }

    private function hydrateSocialClasses(?array $data): array
    {
        if (empty($data)) {
            return [];
        }

        return array_map(function ($c) {
            return new \Tuzy\Domain\Cosmology\ValueObject\SocialClass(
                type: \Tuzy\Domain\Cosmology\Enums\SocialClassType::from($c['type']),
                power: (float) $c['power'],
                contentment: (float) $c['contentment'],
                size: (float) $c['size'],
                name: $c['name'] ?? null // Added name if available
            );
        }, $data);
    }
}
