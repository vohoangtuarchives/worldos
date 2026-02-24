<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Cosmology;

use WorldOS\Legacy\Domain\Cosmology\Repository\WorldRepository;
use WorldOS\Legacy\Domain\Cosmology\Entity\World;
use WorldOS\Legacy\Domain\Cosmology\Entity\Archetype;
use App\Models\World as WorldModel;

class EloquentWorldRepository implements WorldRepository
{
    public function save(World $world): void
    {
        WorldModel::updateOrCreate(
            ['id' => $world->getId()],
            [
                'name' => $world->getName(),
                'type' => 'simulation', 
                'genre' => 'generated',
                'status' => 'active',
                'config' => [
                    'archetype' => $world->getArchetype()->value,
                ]
            ]
        );
    }

    public function findById(string $id): ?World
    {
        $model = WorldModel::find($id);

        if (!$model) {
            return null;
        }

        $config = is_array($model->config) ? $model->config : json_decode($model->config ?? '{}', true);
        
        $archetypeValue = $config['archetype'] ?? 'ascension_mysticism';
        
        $world = clone \unserialize(\sprintf('O:%d:"%s":0:{}', \strlen(World::class), World::class));
        
        $reflection = new \ReflectionClass(World::class);
        
        $idProperty = $reflection->getParentClass()->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($world, $model->id);
        
        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setAccessible(true);
        $nameProperty->setValue($world, $model->name ?? 'Unknown World');
        
        $archProperty = $reflection->getProperty('archetype');
        $archProperty->setAccessible(true);
        $archProperty->setValue($world, Archetype::from($archetypeValue));

        return $world;
    }
}
