<?php

namespace Tuzy\Infrastructure\Persistence\Narrative;

use Tuzy\Domain\Narrative\Repository\MythRepository;
use Tuzy\Domain\Narrative\Entity\Myth;
use App\Models\Myth as MythModel;

class EloquentMythRepository implements MythRepository
{
    public function save(Myth $myth): void
    {
        $reflection = new \ReflectionClass(Myth::class);
        $worldIdProp = $reflection->getProperty('worldId'); $worldIdProp->setAccessible(true);
        $titleProp = $reflection->getProperty('title'); $titleProp->setAccessible(true);
        $contentProp = $reflection->getProperty('content'); $contentProp->setAccessible(true);
        $believabilityProp = $reflection->getProperty('believability'); $believabilityProp->setAccessible(true);

        MythModel::updateOrCreate(
            ['id' => $myth->getId()],
            [
                'world_id' => $worldIdProp->getValue($myth),
                'title' => $titleProp->getValue($myth),
                'content' => $contentProp->getValue($myth),
                'status' => 'active',
                'believability' => $believabilityProp->getValue($myth) // Cần đảm bảo table này có cột hoặc lưu vào json
            ]
        );
    }

    public function findByWorld(string $worldId): array
    {
        $models = MythModel::where('world_id', $worldId)->get();
        $results = [];
        
        foreach ($models as $model) {
            $myth = clone \unserialize(\sprintf('O:%d:"%s":0:{}', \strlen(Myth::class), Myth::class));
            $reflection = new \ReflectionClass(Myth::class);
            $idProp = $reflection->getParentClass()->getProperty('id'); $idProp->setAccessible(true);
            $idProp->setValue($myth, $model->id);
            
            $worldIdProp = $reflection->getProperty('worldId'); $worldIdProp->setAccessible(true);
            $worldIdProp->setValue($myth, $model->world_id);
            
            $titleProp = $reflection->getProperty('title'); $titleProp->setAccessible(true);
            $titleProp->setValue($myth, $model->title);
            
            $contentProp = $reflection->getProperty('content'); $contentProp->setAccessible(true);
            $contentProp->setValue($myth, $model->content);
            
            $believabilityProp = $reflection->getProperty('believability'); $believabilityProp->setAccessible(true);
            $believabilityProp->setValue($myth, $model->believability ?? 1.0);
            
            $results[] = $myth;
        }
        return $results;
    }
}
