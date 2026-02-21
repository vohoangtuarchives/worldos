<?php

namespace WorldOS\Infrastructure\Persistence\Material;

use WorldOS\Domains\Material\CharacterRepository;
use WorldOS\Domains\Material\Character;
use App\Models\Character as CharacterModel;

class EloquentCharacterRepository implements CharacterRepository
{
    public function save(Character $character): void
    {
        // Trích xuất properties bằng Reflection
        $reflection = new \ReflectionClass(Character::class);
        $nameProp = $reflection->getProperty('name'); $nameProp->setAccessible(true);
        $factionProp = $reflection->getProperty('factionId'); $factionProp->setAccessible(true);
        $aliveProp = $reflection->getProperty('isAlive'); $aliveProp->setAccessible(true);
        
        $survivalProp = $reflection->getProperty('baseSurvival'); $survivalProp->setAccessible(true);
        $riskProp = $reflection->getProperty('riskFactors'); $riskProp->setAccessible(true);
        $narrativeProp = $reflection->getProperty('narrativeWeight'); $narrativeProp->setAccessible(true);

        CharacterModel::updateOrCreate(
            ['id' => $character->getId()],
            [
                'name' => $nameProp->getValue($character),
                'faction_id' => $factionProp->getValue($character),
                'status' => $aliveProp->getValue($character) ? 'alive' : 'dead',
                // Lưu ValueObjects dưới dạng mảng JSON
                'attributes' => [
                    'survival_rate' => $survivalProp->getValue($character)->value(),
                    'risk_factors' => [
                        'injury' => $riskProp->getValue($character)->injuryState,
                        'environment' => $riskProp->getValue($character)->environmentalDanger,
                    ]
                ]
            ]
        );
    }

    public function findById(string $id): ?Character
    {
        $model = CharacterModel::find($id);
        if (!$model) return null;
        return $this->toDomain($model);
    }

    public function findAliveByFaction(string $factionId): array
    {
        $models = CharacterModel::where('faction_id', $factionId)
            ->where('status', 'alive')
            ->get();
            
        return $models->map(fn($m) => $this->toDomain($m))->all();
    }

    private function toDomain(CharacterModel $model): Character
    {
        $reflection = new \ReflectionClass(Character::class);
        $character = $reflection->newInstanceWithoutConstructor();
        
        $idProp = $reflection->getParentClass()->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($character, $model->id);
        
        $nameProp = $reflection->getProperty('name');
        $nameProp->setAccessible(true);
        $nameProp->setValue($character, $model->name);
        
        $factionProp = $reflection->getProperty('factionId');
        $factionProp->setAccessible(true);
        $factionProp->setValue($character, $model->faction_id);
        
        $aliveProp = $reflection->getProperty('isAlive');
        $aliveProp->setAccessible(true);
        $aliveProp->setValue($character, $model->status === 'alive');

        // Bỏ qua load chi tiết các VO phức tạp tạm thời để tránh exception vì Model cũ khác schema
        return $character;
    }
}
