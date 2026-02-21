<?php

namespace WorldOS\Infrastructure\Persistence\Evolution;

use WorldOS\Domains\Evolution\WorldStateRepository;
use WorldOS\Domains\Evolution\WorldState;
use WorldOS\Domains\Evolution\Tension;
use WorldOS\Domains\Evolution\ValueObjects\CoreTruth;
use WorldOS\Domains\Evolution\ValueObjects\Axiom;
use App\Models\WorldState as WorldStateModel;

class EloquentWorldStateRepository implements WorldStateRepository
{
    public function save(WorldState $worldState): void
    {
        $tensionsArray = array_map(function($t) {
            return [
                'domain' => $t->domain,
                'level' => $t->level,
                'source' => $t->source
            ];
        }, $worldState->getTensions());

        $coreTruthAxioms = array_map(function($a) {
            return [
                'id' => $a->id,
                'description' => $a->description,
                'isAbsolute' => $a->isAbsolute
            ];
        }, $worldState->getCoreTruth()->getAxioms());

        WorldStateModel::updateOrCreate(
            ['world_id' => $worldState->getId()],
            [
                'state_vector' => [
                    'global_entropy' => $worldState->getEntropy(),
                    'tensions' => $tensionsArray,
                    'core_truth' => $coreTruthAxioms
                ]
            ]
        );
    }

    public function findById(string $worldId): ?WorldState
    {
        $model = WorldStateModel::where('world_id', $worldId)->first();

        if (!$model) {
            return null;
        }

        $stateVector = is_array($model->state_vector) ? $model->state_vector : json_decode($model->state_vector ?? '{}', true);
        $globalEntropy = $stateVector['global_entropy'] ?? 0.0;
        
        $axiomsData = $stateVector['core_truth'] ?? [];
        $axioms = [];
        foreach ($axiomsData as $aData) {
            $axioms[] = new Axiom($aData['id'], $aData['description'], $aData['isAbsolute'] ?? true);
        }
        $coreTruth = new CoreTruth($axioms);

        $worldState = clone \unserialize(\sprintf('O:%d:"%s":0:{}', \strlen(WorldState::class), WorldState::class));
        $reflection = new \ReflectionClass(WorldState::class);
        
        $idProp = $reflection->getParentClass()->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($worldState, $model->world_id);
        
        $entropyProp = $reflection->getProperty('globalEntropy');
        $entropyProp->setAccessible(true);
        $entropyProp->setValue($worldState, $globalEntropy);
        
        $tensions = [];
        $tensionsData = $stateVector['tensions'] ?? [];
        foreach ($tensionsData as $tData) {
            $tensions[] = new Tension(
                $tData['domain'],
                $tData['level'],
                $tData['source']
            );
        }
        $tensionsProp = $reflection->getProperty('tensions');
        $tensionsProp->setAccessible(true);
        $tensionsProp->setValue($worldState, $tensions);
        
        $coreTruthProp = $reflection->getProperty('coreTruth');
        $coreTruthProp->setAccessible(true);
        $coreTruthProp->setValue($worldState, $coreTruth);

        $worldState->releaseEvents();

        return $worldState;
    }
}
