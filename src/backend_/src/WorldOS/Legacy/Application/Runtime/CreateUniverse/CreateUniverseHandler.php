<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\CreateUniverse;

use WorldOS\Legacy\Domain\Runtime\Entity\Universe;
use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;
use WorldOS\Legacy\Application\Saga\Services\GenesisPresetService;

final class CreateUniverseHandler
{
    public function __construct(
        private readonly UniverseRepositoryInterface $universeRepository,
        private readonly \WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface $worldRepository,
    ) {
    }

    public function handle(CreateUniverseCommand $command): CreateUniverseResult
    {
        $stateVector = [];
        
        // Fetch the parent World to inherit its genetic seed configuration
        $world = $this->worldRepository->findById($command->worldId);
        if ($world) {
            $geneVector = $world->getGeneVector();
            if (is_array($geneVector) && isset($geneVector['seed_vector'])) {
                $seedVector = $geneVector['seed_vector'];
                foreach (['ontology', 'epistemic', 'civilization', 'energy'] as $category) {
                    if (isset($seedVector[$category])) {
                        foreach ($seedVector[$category] as $dim => $value) {
                            if (is_array($value) && count($value) === 2) {
                                $stateVector[$dim] = mt_rand((int)($value[0] * 1000), (int)($value[1] * 1000)) / 1000;
                            } else {
                                $stateVector[$dim] = $value;
                            }
                        }
                    }
                }
            }
        }


        $universe = Universe::create(
            $command->name,
            $command->worldId,
            $command->sagaId,
            null, // id
            0, // age
            'running', // status
            $stateVector, // stateVector
            0.0, // entropy
            1.0 // stabilityIndex
        );
        $this->universeRepository->save($universe);
        
        return new CreateUniverseResult($universe->getId(), $universe->getName());
    }
}
