<?php

namespace WorldOS\Legacy\Infrastructure\Persistence\Material;

use WorldOS\Legacy\Domain\Material\Repository\FactionRepository;
use WorldOS\Legacy\Domain\Material\Entity\Faction;
use App\Models\Faction as FactionModel;
use WorldOS\Legacy\Domain\Material\ValueObject\IdeologyVector;
use WorldOS\Legacy\Domain\Material\ValueObject\FactionMemory;

class EloquentFactionRepository implements FactionRepository
{
    public function save(Faction $faction): void
    {
        $reflection = new \ReflectionClass(Faction::class);
        $nameProp = $reflection->getProperty('name'); $nameProp->setAccessible(true);
        $worldIdProp = $reflection->getProperty('worldId'); $worldIdProp->setAccessible(true);
        $ideologyProp = $reflection->getProperty('ideology'); $ideologyProp->setAccessible(true);
        $memoryProp = $reflection->getProperty('memory'); $memoryProp->setAccessible(true);
        
        $ideology = $ideologyProp->getValue($faction);
        $memory = $memoryProp->getValue($faction);

        FactionModel::updateOrCreate(
            ['id' => $faction->getId()],
            [
                'name' => $nameProp->getValue($faction),
                'world_id' => $worldIdProp->getValue($faction),
                'ideology_vector' => [
                    'order' => $ideology->order,
                    'chaos' => $ideology->chaos,
                    'tradition' => $ideology->tradition,
                    'progress' => $ideology->progress,
                ],
                'memory_state' => [
                    'retention_rate' => $memory->retentionRate,
                    'recorded_intents' => $memory->recordedIntents
                ]
            ]
        );
    }

    public function findById(string $id): ?Faction
    {
        $model = FactionModel::find($id);
        if (!$model) return null;

        $ideologyData = is_array($model->ideology_vector) ? $model->ideology_vector : json_decode($model->ideology_vector ?? '{}', true);
        $memoryData = is_array($model->memory_state) ? $model->memory_state : json_decode($model->memory_state ?? '{}', true);

        $ideology = new IdeologyVector(
            $ideologyData['order'] ?? 0.5,
            $ideologyData['chaos'] ?? 0.5,
            $ideologyData['tradition'] ?? 0.5,
            $ideologyData['progress'] ?? 0.5
        );

        $memory = new FactionMemory($memoryData['retention_rate'] ?? 1.0);
        foreach ($memoryData['recorded_intents'] ?? [] as $intent) {
            $memory->recordIntent($intent);
        }

        $faction = clone \unserialize(\sprintf('O:%d:"%s":0:{}', \strlen(Faction::class), Faction::class));
        $reflection = new \ReflectionClass(Faction::class);
        $idProp = $reflection->getParentClass()->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($faction, $model->id);
        
        $nameProp = $reflection->getProperty('name'); $nameProp->setAccessible(true);
        $nameProp->setValue($faction, $model->name);
        
        $worldIdProp = $reflection->getProperty('worldId'); $worldIdProp->setAccessible(true);
        $worldIdProp->setValue($faction, $model->world_id);
        
        $ideologyProp = $reflection->getProperty('ideology'); $ideologyProp->setAccessible(true);
        $ideologyProp->setValue($faction, $ideology);
        
        $memoryProp = $reflection->getProperty('memory'); $memoryProp->setAccessible(true);
        $memoryProp->setValue($faction, $memory);

        return $faction;
    }
}
