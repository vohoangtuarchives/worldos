<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Cosmology;

use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseRepository;
use WorldOS\Legacy\Domain\Cosmology\Entity\Universe;
use WorldOS\Legacy\Domain\Cosmology\Entity\WorldSeed;
use WorldOS\Legacy\Domain\Cosmology\Entity\Archetype;
use App\Models\UniverseModel;

class EloquentUniverseRepository implements UniverseRepository
{
    public function save(Universe $universe): void
    {
        $seed = $universe->getSeed();
        
        UniverseModel::updateOrCreate(
            ['id' => $universe->getId()],
            [
                'name' => 'Universe ' . substr($universe->getId(), 0, 8),
                'parameters' => [
                    'archetype' => $seed->archetype->value,
                    'ontology' => $seed->ontologyVector,
                    'epistemic' => $seed->epistemicVector,
                    'civilization' => $seed->civilizationVector,
                    'energy' => $seed->energyVector,
                ],
                'status' => 'ignited'
            ]
        );
    }

    public function findById(string $id): ?Universe
    {
        $model = UniverseModel::find($id);

        if (!$model) {
            return null;
        }

        $params = $model->parameters ?? [];
        $params = is_array($params) ? $params : json_decode($params, true);
        
        $seed = new WorldSeed(
            Archetype::from($params['archetype'] ?? 'ascension_mysticism'),
            $params['ontology'] ?? 0.5,
            $params['epistemic'] ?? 0.5,
            $params['civilization'] ?? 0.5,
            $params['energy'] ?? 0.5
        );

        // Reflection to bypass private constructor
        $reflection = new \ReflectionClass(Universe::class);
        $universe = $reflection->newInstanceWithoutConstructor();
        
        $idProperty = $reflection->getParentClass()->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($universe, $model->id);
        
        $seedProperty = $reflection->getProperty('seed');
        $seedProperty->setAccessible(true);
        $seedProperty->setValue($universe, $seed);

        return $universe;
    }
}
