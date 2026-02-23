<?php

namespace WorldOS\Legacy\Application\Runtime\Evaluation;

use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Domain\Runtime\ValueObject\EvaluationResult;
use WorldOS\Legacy\Infrastructure\Cosmology\Repositories\CosmologyRepository;
use WorldOS\Legacy\Infrastructure\Cosmology\Repositories\UniverseSnapshotRepository;
use WorldOS\Legacy\Application\Evolution\Kernel\WorldEvolutionKernel;
use App\Models\UniverseModel;
use App\Models\World;

/**
 * WorldOS v3 Phase 3: Execute recommendation from EvaluationResult (fork / archive / continue).
 */
class DecisionEngine
{
    public function __construct(
        private UniverseSnapshotRepository $snapshotRepository,
        private CosmologyRepository $cosmologyRepository,
        private WorldEvolutionKernel $kernel
    ) {
    }

    public function execute(Universe $universe, EvaluationResult $result): string
    {
        if ($result->recommendation === EvaluationResult::RECOMMENDATION_ARCHIVE) {
            $this->archive($universe);
            return 'archive';
        }
        if ($result->recommendation === EvaluationResult::RECOMMENDATION_FORK) {
            // Forking is handled by SagaService to avoid circular dependency
            return 'fork';
        }
        if ($result->mutationSuggestion !== null) {
            $model = UniverseModel::find($universe->getId());
            $world = $model && $model->world_id ? World::find($model->world_id) : null;
            if ($world && $this->kernel->validateMutation($world, $result->mutationSuggestion)) {
                $this->kernel->applyPressure($universe, $result->mutationSuggestion->type, $result->mutationSuggestion->intensity);
                $this->cosmologyRepository->save($universe, $model->world_id);
            }
        }
        return 'continue';
    }

    private function archive(Universe $universe): void
    {
        UniverseModel::where('id', $universe->getId())->update(['status' => 'archived']);
    }
}
