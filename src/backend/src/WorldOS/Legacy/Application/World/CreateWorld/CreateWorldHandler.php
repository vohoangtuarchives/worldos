<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\CreateWorld;

use WorldOS\Blueprint\Domain\Legacy\Entity\World;
use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;
use WorldOS\Legacy\Application\Saga\Services\GenesisPresetService;

final class CreateWorldHandler
{
    public function __construct(
        private readonly WorldRepositoryInterface $worldRepository,
        private readonly GenesisPresetService $presetService,
    ) {
    }

    public function handle(CreateWorldCommand $command): CreateWorldResult
    {
        $presetData = $this->presetService->find($command->preset) ?? [];
        $geneVector = [];
        $config = [];
        
        if (!empty($presetData)) {
            $config['preset_key'] = $command->preset;
            $config['archetype'] = $presetData['archetype'] ?? null;
            $config['seed_vector'] = $presetData['seed_vector'] ?? null;
            $config['drift_profile'] = $presetData['drift_profile'] ?? null;
            $config['genre'] = $presetData['genre'] ?? null;
            $config['power_system'] = $presetData['power_system'] ?? null;

            $geneVector['archetype'] = $presetData['archetype'] ?? null;
            $geneVector['seed_vector'] = $presetData['seed_vector'] ?? null;
            $geneVector['power_system'] = $presetData['power_system'] ?? null;
            $geneVector['tech_level'] = $presetData['tech_level'] ?? null;
        }

        $world = World::create(
            $command->name,
            null, // id
            'active', // status
            'healthy', // healthStatus
            0, // currentTick
            $command->originType,
            $command->preset,
            $config,
            $geneVector
        );
        $this->worldRepository->save($world);
        return new CreateWorldResult($world->getId(), $world->getName());
    }
}
